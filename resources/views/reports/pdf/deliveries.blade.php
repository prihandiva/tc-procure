@extends('reports.pdf.layout')

@section('content')
    <table class="data-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 90px;">Kode DO</th>
                <th style="width: 90px;">No SJ Vendor</th>
                <th style="width: 80px;">No PO Acuan</th>
                <th>Rekanan Vendor</th>
                <th style="width: 70px;" class="text-center">Tgl Kirim</th>
                <th style="width: 60px;" class="text-right">Qty Kirim</th>
                <th style="width: 60px;" class="text-right">Qty Terima</th>
                <th style="width: 75px;" class="text-center">Status TC</th>
                <th style="width: 75px;" class="text-center">Penagihan</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totSent = 0;
                $totReceived = 0;
            @endphp
            @forelse ($data as $idx => $dlv)
                @php
                    $sent = (float) $dlv->total_qty_sent;
                    $received = (float) $dlv->total_qty_received;
                    $totSent += $sent;
                    $totReceived += $received;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-bold">{{ $dlv->code }}</td>
                    <td>{{ $dlv->vendor_do_number ?? '-' }}</td>
                    <td>{{ $dlv->purchaseOrder?->po_number ?? '-' }}</td>
                    <td>{{ $dlv->vendor?->name ?? '-' }}</td>
                    <td class="text-center">{{ $dlv->delivery_date->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($sent, 2, ',', '.') }}</td>
                    <td class="text-right text-bold">{{ number_format($received, 2, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge {{ $dlv->status->value === 'confirmed' ? 'badge-green' : ($dlv->status->value === 'rejected' ? 'badge-rose' : 'badge-amber') }}">
                            {{ $dlv->status->label() }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $dlv->isFullyInvoiced() ? 'badge-indigo' : 'badge-amber' }}">
                            {{ $dlv->isFullyInvoiced() ? 'Sudah Ditagih' : 'Belum Ditagih' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted" style="padding: 15px;">Tidak ada data pengiriman fisik sesuai filter yang dipilih.</td>
                </tr>
            @endforelse

            @if(count($data) > 0)
                <tr class="total-row">
                    <td colspan="6" class="text-right text-bold">TOTAL:</td>
                    <td class="text-right text-bold">{{ number_format($totSent, 2, ',', '.') }}</td>
                    <td class="text-right text-bold">{{ number_format($totReceived, 2, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection

