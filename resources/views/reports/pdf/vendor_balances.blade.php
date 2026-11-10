@extends('reports.pdf.layout')

@section('content')
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th>Nama Rekanan Vendor</th>
                <th style="width: 45px;" class="text-center">PO</th>
                <th style="width: 85px;" class="text-right">Plafon PO (Rp)</th>
                <th style="width: 85px;" class="text-right">Diterima Fisik (Rp)</th>
                <th style="width: 85px;" class="text-right">Sisa Kuota (Rp)</th>
                <th style="width: 85px;" class="text-right">Ditagihkan (Rp)</th>
                <th style="width: 85px;" class="text-right">Lunas SAP (Rp)</th>
                <th style="width: 85px;" class="text-right">Sisa Hutang (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totPlafon = 0;
                $totDelivered = 0;
                $totRemaining = 0;
                $totInvoiced = 0;
                $totPaid = 0;
                $totUnpaid = 0;
            @endphp
            @forelse ($data as $idx => $v)
                @php
                    $totPlafon += $v['po_value'];
                    $totDelivered += $v['delivered_value'];
                    $totRemaining += $v['remaining_value'];
                    $totInvoiced += $v['invoiced_value'];
                    $totPaid += $v['paid_value'];
                    $totUnpaid += $v['unpaid_value'];
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-bold">{{ $v['name'] }}</td>
                    <td class="text-center">{{ $v['active_po_count'] }}</td>
                    <td class="text-right">{{ number_format($v['po_value'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($v['delivered_value'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($v['remaining_value'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($v['invoiced_value'], 0, ',', '.') }}</td>
                    <td class="text-right text-bold" style="color: #166534;">{{ number_format($v['paid_value'], 0, ',', '.') }}</td>
                    <td class="text-right text-bold" style="color: {{ $v['unpaid_value'] > 0 ? '#991b1b' : '#334155' }};">
                        {{ number_format($v['unpaid_value'], 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted" style="padding: 15px;">Tidak ada data rekanan vendor.</td>
                </tr>
            @endforelse

            @if(count($data) > 0)
                <tr class="total-row">
                    <td colspan="3" class="text-right text-bold">TOTAL:</td>
                    <td class="text-right text-bold">{{ number_format($totPlafon, 0, ',', '.') }}</td>
                    <td class="text-right text-bold">{{ number_format($totDelivered, 0, ',', '.') }}</td>
                    <td class="text-right text-bold">{{ number_format($totRemaining, 0, ',', '.') }}</td>
                    <td class="text-right text-bold">{{ number_format($totInvoiced, 0, ',', '.') }}</td>
                    <td class="text-right text-bold">{{ number_format($totPaid, 0, ',', '.') }}</td>
                    <td class="text-right text-bold">{{ number_format($totUnpaid, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection

