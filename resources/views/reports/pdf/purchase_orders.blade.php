@extends('reports.pdf.layout')

@section('content')
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 100px;">Nomor PO</th>
                <th>Rekanan Vendor</th>
                <th style="width: 70px;" class="text-center">Tgl PO</th>
                <th style="width: 55px;" class="text-center">Tipe</th>
                <th style="width: 90px;" class="text-right">Plafon PO (Rp)</th>
                <th style="width: 90px;" class="text-right">Realisasi (Rp)</th>
                <th style="width: 90px;" class="text-right">Sisa Kuota (Rp)</th>
                <th style="width: 65px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totPlafon = 0;
                $totDelivered = 0;
                $totRemaining = 0;
            @endphp
            @forelse ($data as $idx => $po)
                @php
                    $plafon = (int) $po->total_amount;
                    $delivered = (int) $po->total_amount_delivered;
                    $remaining = (int) $po->total_amount_remaining;
                    $totPlafon += $plafon;
                    $totDelivered += $delivered;
                    $totRemaining += $remaining;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-bold">{{ $po->po_number }}</td>
                    <td>{{ $po->vendor?->name ?? '-' }}</td>
                    <td class="text-center">{{ $po->po_date->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $po->type->label() }}</td>
                    <td class="text-right text-bold">{{ number_format($plafon, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($delivered, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($remaining, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $po->status->value === 'active' ? 'badge-green' : ($po->status->value === 'completed' ? 'badge-indigo' : 'badge-amber') }}">
                            {{ $po->status->label() }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted" style="padding: 15px;">Tidak ada data Purchase Order sesuai filter yang dipilih.</td>
                </tr>
            @endforelse

            @if(count($data) > 0)
                <tr class="total-row">
                    <td colspan="5" class="text-right text-bold">TOTAL:</td>
                    <td class="text-right text-bold">{{ number_format($totPlafon, 0, ',', '.') }}</td>
                    <td class="text-right text-bold">{{ number_format($totDelivered, 0, ',', '.') }}</td>
                    <td class="text-right text-bold">{{ number_format($totRemaining, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection

