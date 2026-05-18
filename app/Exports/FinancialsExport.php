<?php

namespace App\Exports;

use App\Models\Financial;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinancialsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Financial::with(['transaction.product'])->get();
    }

    /**
     * Set the headers for the Excel spreadsheet columns.
     */
    public function headings(): array
    {
        return [
            'ID Ledger',
            'Tanggal Pembukuan',
            'Tipe Ledger (Pemasukan/Pengeluaran)',
            'Nominal Transaksi (Rp)',
            'Kategori Deskripsi',
            'Grade Kualitas',
            'Kuantitas Mutasi (Pcs)',
            'SKU Produk Terkait',
            'Nama Produk Terkait',
        ];
    }

    /**
     * Map each row of the financial collection to Excel cells.
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->date,
            strtoupper($row->type),
            $row->amount,
            $row->transaction ? "Stock " . ($row->transaction->type === 'masuk' ? 'Restocked' : 'Sold Out') . " (Mutasi Barang)" : 'Penyesuaian Operasional Manual',
            $row->transaction ? 'Grade ' . $row->transaction->grade : '-',
            $row->transaction ? $row->transaction->quantity : '-',
            $row->transaction ? $row->transaction->product?->sku : '-',
            $row->transaction ? $row->transaction->product?->name : '-',
        ];
    }
}
