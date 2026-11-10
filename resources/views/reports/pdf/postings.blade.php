@extends('reports.pdf.layout')

@section('content')
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 110px;">Nomor Posting SAP</th>
                <th style="width: 75px;" class="text-center">Tgl Posting</th>
                <th style="width: 100px;" class="text-right">Realisasi Bayar (Rp)</th>
                <th style="width: 120px;">Nomor PR Terkait</th>
                <th>Invoice & Vendor Terkait</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totPaid = 0;
            @endphp
            @forelse ($data as $idx => $pst)
                @php
                    $paid = (int) $pst->total_paid;
                    $totPaid += $paid;
                    $prNumbers = $pst->paymentRequests->pluck('number')->implode(', ');
                    $invNumbers = $pst->paymentRequests->flatMap->invoices->map(fn($i) => $i->invoice_number . ' (' . ($i->vendor?->name ?? '-') . ')')->implode(', ');
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-bold">{{ $pst->posting_number }}</td>
                    <td class="text-center">{{ $pst->posting_date->format('d/m/Y') }}</td>
                    <td class="text-right text-bold">{{ number_format($paid, 0, ',', '.') }}</td>
                    <td>{{ $prNumbers ?: '-' }}</td>
                    <td>{{ $invNumbers ?: '-' }}</td>
                    <td class="text-muted">{{ $pst->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-muted" style="padding: 15px;">Tidak ada data Dokumen Posting SAP sesuai filter yang dipilih.</td>
                </tr>
            @endforelse

            @if(count($data) > 0)
                <tr class="total-row">
                    <td colspan="3" class="text-right text-bold">TOTAL:</td>
                    <td class="text-right text-bold">{{ number_format($totPaid, 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection

