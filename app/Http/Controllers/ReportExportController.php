<?php

namespace App\Http\Controllers;

use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportExportController extends Controller
{
    public function __construct(
        protected ReportExportService $reportExportService
    ) {}

    /**
     * Handle export request for Excel or PDF
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|string|in:po,deliveries,invoices,postings,vendor_balances',
            'format' => 'required|string|in:excel,pdf',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'vendor_id' => 'nullable|string',
            'status' => 'nullable|string',
            'inline' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $reportType = $validated['report_type'];
        $format = $validated['format'];
        $inline = (bool) ($validated['inline'] ?? false);

        $filters = [
            'date_from' => $validated['date_from'] ?? null,
            'date_to' => $validated['date_to'] ?? null,
            'vendor_id' => $validated['vendor_id'] ?? null,
            'status' => $validated['status'] ?? null,
        ];

        if ($format === 'excel') {
            return $this->reportExportService->generateExcel($reportType, $filters, $user);
        }

        return $this->reportExportService->generatePdf($reportType, $filters, $user, $inline);
    }
}

