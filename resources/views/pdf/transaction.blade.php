<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invois Mutasi #TX{{ $transaction->id }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.4;
            font-size: 13px;
            margin: 0;
            padding: 0;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
        }
        .header {
            border-bottom: 2px solid #6366f1;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4f46e5;
        }
        .company-info {
            text-align: right;
            font-size: 11px;
            color: #666;
        }
        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e1b4b;
            margin-top: 5px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 30px;
        }
        .details-table th {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            font-size: 11px;
            text-transform: uppercase;
            color: #475569;
        }
        .details-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .meta-table td {
            padding: 4px 0;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 10px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-masuk {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-keluar {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-grade {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .notes-area {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            padding: 12px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 12px;
        }
        .footer-signatures {
            margin-top: 80px;
            width: 100%;
        }
        .footer-signatures td {
            text-align: center;
            width: 50%;
        }
        .signature-line {
            width: 150px;
            border-bottom: 1px solid #333;
            margin: 50px auto 5px auto;
        }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <table>
                <tr>
                    <td>
                        <div class="logo">SMART INVENTORY</div>
                        <div class="invoice-title">Surat Jalan / Invois Mutasi</div>
                    </td>
                    <td class="company-info">
                        <strong>Gudang Utama Smart Inventory</strong><br>
                        Kawasan Industri Laragon, Blok C-12<br>
                        Banten, Indonesia<br>
                        support@smartinventory.com
                    </td>
                </tr>
            </table>
        </div>

        <table class="meta-table">
            <tr>
                <td style="width: 15%;"><strong>ID Transaksi</strong></td>
                <td style="width: 35%;">: #TX{{ $transaction->id }}</td>
                <td style="width: 15%;"><strong>Tanggal</strong></td>
                <td style="width: 35%;">: {{ $transaction->created_at->format('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td><strong>Tipe Mutasi</strong></td>
                <td>: 
                    @if($transaction->type === 'masuk')
                        <span class="badge badge-masuk">Barang Masuk (Restock)</span>
                    @else
                        <span class="badge badge-keluar">Barang Keluar (Jual)</span>
                    @endif
                </td>
                <td><strong>Operator</strong></td>
                <td>: {{ $transaction->user ? $transaction->user->name : 'System' }}</td>
            </tr>
        </table>

        <table class="details-table">
            <thead>
                <tr>
                    <th style="width: 20%;">SKU SKU</th>
                    <th style="width: 40%;">Deskripsi Produk</th>
                    <th style="width: 15%;">Grade</th>
                    <th style="width: 10%; text-align: center;">Qty</th>
                    <th style="width: 15%; text-align: right;">Harga Riil</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="font-family: monospace; font-weight: bold; color: #4f46e5;">{{ $transaction->product->sku }}</td>
                    <td>
                        <strong>{{ $transaction->product->name }}</strong><br>
                        <span style="font-size: 11px; color: #666;">Kategori: {{ $transaction->product->kategori }}</span>
                    </td>
                    <td style="text-align: left;">
                        <span class="badge badge-grade">Grade {{ $transaction->grade }}</span>
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ $transaction->quantity }} pcs</td>
                    <td style="text-align: right; font-weight: bold;">Rp {{ number_format($transaction->harga_aktual, 0, ',', '.') }}</td>
                </tr>
                <tr style="background-color: #f8fafc; font-size: 14px;">
                    <td colspan="3" style="text-align: right; font-weight: bold; border-top: 1px solid #cbd5e1;">TOTAL LEDGER:</td>
                    <td colspan="2" style="text-align: right; font-weight: 900; color: #4f46e5; border-top: 1px solid #cbd5e1;">
                        Rp {{ number_format($transaction->quantity * $transaction->harga_aktual, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        @if($transaction->notes)
        <div class="notes-area">
            <strong>Catatan / Keterangan:</strong><br>
            {{ $transaction->notes }}
        </div>
        @endif

        <table class="footer-signatures">
            <tr>
                <td>
                    Operator Lapangan
                    <div class="signature-line"></div>
                    ( {{ $transaction->user ? $transaction->user->name : 'Operator' }} )
                </td>
                <td>
                    Kepala Gudang / Admin
                    <div class="signature-line"></div>
                    ( Administrator )
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
