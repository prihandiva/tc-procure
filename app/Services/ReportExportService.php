<?php

namespace App\Services;

use App\Enums\DeliveryStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PoStatus;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Posting;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Query data based on report type and applied filters
     */
    public function queryData(string $reportType, array $filters, User $user): Collection|Builder
    {
        $dateFrom = ! empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : null;
        $dateTo = ! empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : null;
        $vendorId = ! empty($filters['vendor_id']) && $filters['vendor_id'] !== 'all' ? (int) $filters['vendor_id'] : null;
        $status = ! empty($filters['status']) && $filters['status'] !== 'all' ? $filters['status'] : null;

        // Security: If user is vendor, enforce vendor scope
        if ($user->isVendor()) {
            $vendorId = $user->vendor_id;
        }

        return match ($reportType) {
            'po' => $this->queryPurchaseOrders($dateFrom, $dateTo, $vendorId, $status),
            'deliveries' => $this->queryDeliveries($dateFrom, $dateTo, $vendorId, $status),
            'invoices' => $this->queryInvoices($dateFrom, $dateTo, $vendorId, $status),
            'postings' => $this->queryPostings($dateFrom, $dateTo),
            'vendor_balances' => $this->queryVendorBalances($vendorId),
            default => collect(),
        };
    }

    private function queryPurchaseOrders(?Carbon $from, ?Carbon $to, ?int $vendorId, ?string $status): Collection
    {
        return PurchaseOrder::with(['vendor', 'poItems'])
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($from, fn ($q) => $q->whereDate('po_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('po_date', '<=', $to))
            ->latest('po_date')
            ->latest('id')
            ->get();
    }

    private function queryDeliveries(?Carbon $from, ?Carbon $to, ?int $vendorId, ?string $status): Collection
    {
        return Delivery::with(['vendor', 'purchaseOrder', 'deliveryItems.poItem'])
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($from, fn ($q) => $q->whereDate('delivery_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('delivery_date', '<=', $to))
            ->latest('delivery_date')
            ->latest('id')
            ->get();
    }

    private function queryInvoices(?Carbon $from, ?Carbon $to, ?int $vendorId, ?string $status): Collection
    {
        return Invoice::with(['vendor', 'purchaseOrder', 'invoiceLines'])
            ->when($vendorId, fn ($q) => $q->where('vendor_id', $vendorId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($from, fn ($q) => $q->whereDate('invoice_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('invoice_date', '<=', $to))
            ->latest('invoice_date')
            ->latest('id')
            ->get();
    }

    private function queryPostings(?Carbon $from, ?Carbon $to): Collection
    {
        return Posting::with(['paymentRequests.invoices.vendor', 'paymentRequests.invoices.purchaseOrder'])
            ->when($from, fn ($q) => $q->whereDate('posting_date', '>=', $from))
            ->when($to, fn ($q) => $q->whereDate('posting_date', '<=', $to))
            ->latest('posting_date')
            ->latest('id')
            ->get();
    }

    private function queryVendorBalances(?int $vendorId): Collection
    {
        return Vendor::active()
            ->when($vendorId, fn ($q) => $q->where('id', $vendorId))
            ->with(['purchaseOrders' => function ($q) {
                $q->where('status', PoStatus::ACTIVE)->with('poItems');
            }])
            ->get()
            ->map(function ($vendor) {
                $pos = $vendor->purchaseOrders;
                $poValue = (int) $pos->sum(fn ($p) => (int) $p->total_amount);
                $deliveredValue = (int) $pos->sum(fn ($p) => (int) $p->total_amount_delivered);
                $remainingValue = max(0, $poValue - $deliveredValue);

                $invoices = Invoice::where('vendor_id', $vendor->id)->get();
                $invoicedValue = (int) $invoices->whereIn('status', [
                    InvoiceStatus::SUBMITTED,
                    InvoiceStatus::APPROVED,
                    InvoiceStatus::IN_PAYMENT,
                    InvoiceStatus::PAID,
                ])->sum(fn ($i) => (int) $i->total_amount);

                $paidValue = (int) $invoices->where('status', InvoiceStatus::PAID)->sum(fn ($i) => (int) $i->total_amount);

                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'active_po_count' => $pos->count(),
                    'po_value' => $poValue,
                    'delivered_value' => $deliveredValue,
                    'remaining_value' => $remainingValue,
                    'invoiced_value' => $invoicedValue,
                    'paid_value' => $paidValue,
                    'unpaid_value' => max(0, $invoicedValue - $paidValue),
                ];
            });
    }

    /**
     * Generate Excel export using PhpSpreadsheet
     */
    public function generateExcel(string $reportType, array $filters, User $user): StreamedResponse
    {
        $data = $this->queryData($reportType, $filters, $user);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $reportTitle = $this->getReportTitle($reportType);
        $sheet->setTitle(substr($reportTitle, 0, 31));

        // 1. Header Information Block
        $sheet->setCellValue('A1', 'TC-PROCURE: SISTEM PENGADAAN TRAINING CENTER');
        $sheet->setCellValue('A2', strtoupper($reportTitle));
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E3A8A'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4338CA'));

        // Filter metadata
        $filterDesc = $this->getFilterDescription($filters, $user);
        $sheet->setCellValue('A3', 'Filter: ' . $filterDesc . ' | Dicetak Pada: ' . now()->translatedFormat('d F Y H:i') . ' WIB oleh: ' . $user->name);
        $sheet->mergeCells('A3:H3');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        $startRow = 5;

        // 2. Populate Headers & Rows per Report Type
        match ($reportType) {
            'po' => $this->buildPoExcel($sheet, $data, $startRow),
            'deliveries' => $this->buildDeliveriesExcel($sheet, $data, $startRow),
            'invoices' => $this->buildInvoicesExcel($sheet, $data, $startRow),
            'postings' => $this->buildPostingsExcel($sheet, $data, $startRow),
            'vendor_balances' => $this->buildVendorBalancesExcel($sheet, $data, $startRow),
            default => null,
        };

        $writer = new Xlsx($spreadsheet);
        $timestamp = now()->format('Ymd_His');
        $fileName = "Laporan_{$reportType}_{$timestamp}.xlsx";

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Generate PDF export using Barryvdh/DomPDF
     */
    public function generatePdf(string $reportType, array $filters, User $user, bool $inline = false)
    {
        $data = $this->queryData($reportType, $filters, $user);
        $reportTitle = $this->getReportTitle($reportType);
        $filterDesc = $this->getFilterDescription($filters, $user);
        $timestamp = now()->format('Ymd_His');
        $fileName = "Laporan_{$reportType}_{$timestamp}.pdf";

        $viewName = match ($reportType) {
            'po' => 'reports.pdf.purchase_orders',
            'deliveries' => 'reports.pdf.deliveries',
            'invoices' => 'reports.pdf.invoices',
            'postings' => 'reports.pdf.postings',
            'vendor_balances' => 'reports.pdf.vendor_balances',
            default => 'reports.pdf.purchase_orders',
        };

        $pdf = Pdf::loadView($viewName, [
            'data' => $data,
            'reportTitle' => $reportTitle,
            'filterDesc' => $filterDesc,
            'filters' => $filters,
            'user' => $user,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        if ($inline) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }

    // --- Excel Sheet Builders ---

    private function buildPoExcel($sheet, Collection $data, int $startRow): void
    {
        $headers = ['No', 'Nomor PO', 'Rekanan Vendor', 'Tanggal PO', 'Tipe', 'Plafon PO (Rp)', 'Realisasi Diterima (Rp)', 'Sisa Kuota (Rp)', 'Status'];
        $this->setTableHeaders($sheet, $headers, $startRow);

        $row = $startRow + 1;
        $totalPlafon = 0;
        $totalDelivered = 0;
        $totalRemaining = 0;

        foreach ($data as $idx => $po) {
            $plafon = (int) $po->total_amount;
            $delivered = (int) $po->total_amount_delivered;
            $remaining = (int) $po->total_amount_remaining;

            $totalPlafon += $plafon;
            $totalDelivered += $delivered;
            $totalRemaining += $remaining;

            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $po->po_number);
            $sheet->setCellValue("C{$row}", $po->vendor?->name ?? '-');
            $sheet->setCellValue("D{$row}", $po->po_date->format('d/m/Y'));
            $sheet->setCellValue("E{$row}", $po->type->label());
            $sheet->setCellValue("F{$row}", $plafon);
            $sheet->setCellValue("G{$row}", $delivered);
            $sheet->setCellValue("H{$row}", $remaining);
            $sheet->setCellValue("I{$row}", $po->status->label());

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Total Row
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue("F{$row}", $totalPlafon);
        $sheet->setCellValue("G{$row}", $totalDelivered);
        $sheet->setCellValue("H{$row}", $totalRemaining);
        $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
        $sheet->getStyle("F{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F1F5F9');

        $this->finalizeSheet($sheet, 'I', $startRow, $row);
    }

    private function buildDeliveriesExcel($sheet, Collection $data, int $startRow): void
    {
        $headers = ['No', 'Kode Pengiriman', 'No SJ Vendor', 'No PO Acuan', 'Rekanan Vendor', 'Tgl Kirim', 'Qty Kirim', 'Qty Diterima', 'Status Verifikasi', 'Status Tagihan'];
        $this->setTableHeaders($sheet, $headers, $startRow);

        $row = $startRow + 1;
        $totalSent = 0;
        $totalReceived = 0;

        foreach ($data as $idx => $dlv) {
            $sent = (float) $dlv->total_qty_sent;
            $received = (float) $dlv->total_qty_received;
            $totalSent += $sent;
            $totalReceived += $received;

            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $dlv->code);
            $sheet->setCellValue("C{$row}", $dlv->vendor_do_number ?? '-');
            $sheet->setCellValue("D{$row}", $dlv->purchaseOrder?->po_number ?? '-');
            $sheet->setCellValue("E{$row}", $dlv->vendor?->name ?? '-');
            $sheet->setCellValue("F{$row}", $dlv->delivery_date->format('d/m/Y'));
            $sheet->setCellValue("G{$row}", $sent);
            $sheet->setCellValue("H{$row}", $received);
            $sheet->setCellValue("I{$row}", $dlv->status->label());
            $sheet->setCellValue("J{$row}", $dlv->isFullyInvoiced() ? 'Sudah Ditagih' : 'Belum Ditagih');

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle("I{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Total Row
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("G{$row}", $totalSent);
        $sheet->setCellValue("H{$row}", $totalReceived);
        $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
        $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F1F5F9');

        $this->finalizeSheet($sheet, 'J', $startRow, $row);
    }

    private function buildInvoicesExcel($sheet, Collection $data, int $startRow): void
    {
        $headers = ['No', 'Nomor Invoice', 'No PO Acuan', 'Rekanan Vendor', 'Tgl Invoice', 'Jatuh Tempo (TOP)', 'Nilai Tagihan (Rp)', '3-Way Match', 'Status Tagihan'];
        $this->setTableHeaders($sheet, $headers, $startRow);

        $row = $startRow + 1;
        $totalAmount = 0;

        foreach ($data as $idx => $inv) {
            $amount = (int) $inv->total_amount;
            $totalAmount += $amount;

            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $inv->invoice_number);
            $sheet->setCellValue("C{$row}", $inv->purchaseOrder?->po_number ?? '-');
            $sheet->setCellValue("D{$row}", $inv->vendor?->name ?? '-');
            $sheet->setCellValue("E{$row}", $inv->invoice_date->format('d/m/Y'));
            $sheet->setCellValue("F{$row}", $inv->due_date ? $inv->due_date->format('d/m/Y') . ($inv->isOverdue() ? ' (Overdue)' : '') : '-');
            $sheet->setCellValue("G{$row}", $amount);
            $sheet->setCellValue("H{$row}", $inv->isThreeWayMatched() ? 'Valid (Match)' : 'Anomali');
            $sheet->setCellValue("I{$row}", $inv->status->label());

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("H{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Total Row
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->setCellValue("G{$row}", $totalAmount);
        $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
        $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F1F5F9');

        $this->finalizeSheet($sheet, 'I', $startRow, $row);
    }

    private function buildPostingsExcel($sheet, Collection $data, int $startRow): void
    {
        $headers = ['No', 'Nomor Posting SAP', 'Tanggal Posting', 'Total Realisasi Bayar (Rp)', 'Nomor PR Terkait', 'Invoice Terkait', 'Catatan'];
        $this->setTableHeaders($sheet, $headers, $startRow);

        $row = $startRow + 1;
        $totalPaid = 0;

        foreach ($data as $idx => $pst) {
            $paid = (int) $pst->total_paid;
            $totalPaid += $paid;

            $prNumbers = $pst->paymentRequests->pluck('number')->implode(', ');
            $invNumbers = $pst->paymentRequests->flatMap->invoices->pluck('invoice_number')->implode(', ');

            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $pst->posting_number);
            $sheet->setCellValue("C{$row}", $pst->posting_date->format('d/m/Y'));
            $sheet->setCellValue("D{$row}", $paid);
            $sheet->setCellValue("E{$row}", $prNumbers ?: '-');
            $sheet->setCellValue("F{$row}", $invNumbers ?: '-');
            $sheet->setCellValue("G{$row}", $pst->notes ?? '-');

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');

            $row++;
        }

        // Total Row
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("D{$row}", $totalPaid);
        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F1F5F9');

        $this->finalizeSheet($sheet, 'G', $startRow, $row);
    }

    private function buildVendorBalancesExcel($sheet, Collection $data, int $startRow): void
    {
        $headers = ['No', 'Nama Rekanan Vendor', 'PO Aktif', 'Plafon PO (Rp)', 'Diterima Fisik (Rp)', 'Sisa Kuota (Rp)', 'Total Ditagih (Rp)', 'Lunas SAP (Rp)', 'Sisa Tagihan (Rp)'];
        $this->setTableHeaders($sheet, $headers, $startRow);

        $row = $startRow + 1;
        $totPlafon = 0;
        $totDelivered = 0;
        $totRemaining = 0;
        $totInvoiced = 0;
        $totPaid = 0;
        $totUnpaid = 0;

        foreach ($data as $idx => $v) {
            $totPlafon += $v['po_value'];
            $totDelivered += $v['delivered_value'];
            $totRemaining += $v['remaining_value'];
            $totInvoiced += $v['invoiced_value'];
            $totPaid += $v['paid_value'];
            $totUnpaid += $v['unpaid_value'];

            $sheet->setCellValue("A{$row}", $idx + 1);
            $sheet->setCellValue("B{$row}", $v['name']);
            $sheet->setCellValue("C{$row}", $v['active_po_count']);
            $sheet->setCellValue("D{$row}", $v['po_value']);
            $sheet->setCellValue("E{$row}", $v['delivered_value']);
            $sheet->setCellValue("F{$row}", $v['remaining_value']);
            $sheet->setCellValue("G{$row}", $v['invoiced_value']);
            $sheet->setCellValue("H{$row}", $v['paid_value']);
            $sheet->setCellValue("I{$row}", $v['unpaid_value']);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}:I{$row}")->getNumberFormat()->setFormatCode('#,##0');

            $row++;
        }

        // Total Row
        $sheet->setCellValue("A{$row}", 'TOTAL');
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue("D{$row}", $totPlafon);
        $sheet->setCellValue("E{$row}", $totDelivered);
        $sheet->setCellValue("F{$row}", $totRemaining);
        $sheet->setCellValue("G{$row}", $totInvoiced);
        $sheet->setCellValue("H{$row}", $totPaid);
        $sheet->setCellValue("I{$row}", $totUnpaid);

        $sheet->getStyle("A{$row}:I{$row}")->getFont()->setBold(true);
        $sheet->getStyle("D{$row}:I{$row}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F1F5F9');

        $this->finalizeSheet($sheet, 'I', $startRow, $row);
    }

    private function setTableHeaders($sheet, array $headers, int $row): void
    {
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}{$row}", $header);
            $col++;
        }

        $lastCol = chr(ord('A') + count($headers) - 1);
        $headerRange = "A{$row}:{$lastCol}{$row}";

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '312E81'], // Indigo 900
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(26);
    }

    private function finalizeSheet($sheet, string $lastCol, int $startRow, int $lastRow): void
    {
        $tableRange = "A{$startRow}:{$lastCol}{$lastRow}";

        // Add thin borders
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->applyFromArray([
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['argb' => 'CBD5E1'],
        ]);

        // Auto size columns
        foreach (range('A', $lastCol) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    public function getReportTitle(string $type): string
    {
        return match ($type) {
            'po' => 'Laporan Rekapitulasi Purchase Order (PO)',
            'deliveries' => 'Laporan Pengiriman Barang (Surat Jalan DO)',
            'invoices' => 'Laporan Tagihan & Validasi 3-Way Match (Invoice)',
            'postings' => 'Laporan Dokumen Posting SAP & Realisasi Bayar',
            'vendor_balances' => 'Laporan Rekapitulasi Saldo & Pengadaan Rekanan',
            default => 'Laporan Pengadaan',
        };
    }

    public function getFilterDescription(array $filters, User $user): string
    {
        $parts = [];

        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $parts[] = 'Periode: ' . Carbon::parse($filters['date_from'])->format('d/m/Y') . ' s/d ' . Carbon::parse($filters['date_to'])->format('d/m/Y');
        } elseif (! empty($filters['date_from'])) {
            $parts[] = 'Mulai: ' . Carbon::parse($filters['date_from'])->format('d/m/Y');
        } elseif (! empty($filters['date_to'])) {
            $parts[] = 'Sampai: ' . Carbon::parse($filters['date_to'])->format('d/m/Y');
        } else {
            $parts[] = 'Semua Periode (Tanpa Filter Tanggal)';
        }

        if ($user->isVendor()) {
            $parts[] = 'Rekanan: ' . ($user->vendor?->name ?? 'Vendor Anda');
        } elseif (! empty($filters['vendor_id']) && $filters['vendor_id'] !== 'all') {
            $v = Vendor::find($filters['vendor_id']);
            $parts[] = 'Rekanan: ' . ($v?->name ?? 'ID #' . $filters['vendor_id']);
        } else {
            $parts[] = 'Semua Rekanan';
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $parts[] = 'Status: ' . ucfirst($filters['status']);
        } else {
            $parts[] = 'Semua Status';
        }

        return implode(' • ', $parts);
    }
}

