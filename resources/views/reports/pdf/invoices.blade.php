@extends('reports.pdf.layout')

@section('content')
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 100px;">Nomor Invoice</th>
                <th style="width: 80px;">No PO Acuan</th>
                <th>Rekanan Vendor</th>
                <th style="width: 65px;" class="text-center">Tgl Invoice</th>
                <th style="width: 75px;" class="text-center">Jatuh Tempo</th>
                <th style="width: 95px;" class="text-right">Total Tagihan (Rp)</th>
                <th style="width: 65px;" class="text-center">3-Way Match</th>
                <th style="width: 75px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totAmount = 0;
            @endphp
            @forelse ($data as $idx => $inv)
                @php
                    $amount = (int) $inv->total_amount;
                    $totAmount += $amount;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-bold">{{ $inv->invoice_number }}</td>
                    <td>{{ $inv->purchaseOrder?->po_number ?? '-' }}</td>
                    <td>{{ $inv->vendor?->name ?? '-' }}</td>
                    <td class="text-center">{{ $inv->invoice_date->format('d/m/Y') }}</td>
                    <td class="text-center">
                        {{ $inv->due_date?->format('d/m/Y') ?? '-' }}
                        @if ($inv->isOverdue())
                            <span class="badge badge-rose" style="display: block; margin-top: 2px;">Overdue {{ $inv->days_overdue }}h</span>
                        @endif
                    </td>
                    <td class="text-right text-bold">{{ number_format($amount, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $inv->isThreeWayMatched() ? 'badge-green' : 'badge-rose' }}">
                            {{ $inv->isThreeWayMatched() ? 'Match Valid' : 'Anomali' }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $inv->status->value === 'paid' ? 'badge-green' : ($inv->status->value === 'approved' ? 'badge-indigo' : ($inv->status->value === 'rejected' ? 'badge-rose' : 'badge-amber')) }}">
                            {{ $inv->status->label() }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted" style="padding: 15px;">Tidak ada data tagihan invoice sesuai filter yang dipilih.</td>
                </tr>
            @endforelse

            @if(count($data) > 0)
                <tr class="total-row">
                    <td colspan="6" class="text-right text-bold">TOTAL:</td>
                    <td class="text-right text-bold">{{ number_format($totAmount, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection

