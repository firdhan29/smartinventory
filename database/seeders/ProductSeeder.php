<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'sku' => 'ELK-TV001',
                'name' => 'TV LED 32 Inch',
                'kategori' => 'Elektronik',
                'foto' => null,
                'harga_beli' => 1500000.00,
                'harga_jual' => 1950000.00,
                'stocks' => [
                    ['grade' => 'A', 'quantity' => 15, 'rak_lokasi' => 'Rak A-01'],
                    ['grade' => 'B', 'quantity' => 3, 'rak_lokasi' => 'Rak B-01'],
                    ['grade' => 'Reject', 'quantity' => 1, 'rak_lokasi' => 'Rak R-01'],
                ]
            ],
            [
                'sku' => 'ELK-HP002',
                'name' => 'Smartphone Android Pro',
                'kategori' => 'Elektronik',
                'foto' => null,
                'harga_beli' => 2800000.00,
                'harga_jual' => 3499000.00,
                'stocks' => [
                    ['grade' => 'A', 'quantity' => 25, 'rak_lokasi' => 'Rak A-02'],
                    ['grade' => 'B', 'quantity' => 5, 'rak_lokasi' => 'Rak B-02'],
                    ['grade' => 'Reject', 'quantity' => 2, 'rak_lokasi' => 'Rak R-01'],
                ]
            ],
            [
                'sku' => 'PKN-BJK003',
                'name' => 'Baju Katun Premium',
                'kategori' => 'Pakaian',
                'foto' => null,
                'harga_beli' => 25000.00,
                'harga_jual' => 45000.00,
                'stocks' => [
                    ['grade' => 'A', 'quantity' => 100, 'rak_lokasi' => 'Rak A-03'],
                    ['grade' => 'B', 'quantity' => 15, 'rak_lokasi' => 'Rak B-03'],
                ]
            ],
            [
                'sku' => 'PKN-JPT004',
                'name' => 'Jaket Denim Pria',
                'kategori' => 'Pakaian',
                'foto' => null,
                'harga_beli' => 120000.00,
                'harga_jual' => 185000.00,
                'stocks' => [
                    ['grade' => 'A', 'quantity' => 50, 'rak_lokasi' => 'Rak A-04'],
                    ['grade' => 'B', 'quantity' => 10, 'rak_lokasi' => 'Rak B-04'],
                    ['grade' => 'Reject', 'quantity' => 1, 'rak_lokasi' => 'Rak R-02'],
                ]
            ],
            [
                'sku' => 'ASK-SPT005',
                'name' => 'Sepatu Olahraga Running',
                'kategori' => 'Alas Kaki',
                'foto' => null,
                'harga_beli' => 180000.00,
                'harga_jual' => 260000.00,
                'stocks' => [
                    ['grade' => 'A', 'quantity' => 40, 'rak_lokasi' => 'Rak A-05'],
                    ['grade' => 'B', 'quantity' => 8, 'rak_lokasi' => 'Rak B-05'],
                    ['grade' => 'Reject', 'quantity' => 3, 'rak_lokasi' => 'Rak R-02'],
                ]
            ],
        ];

        foreach ($products as $pData) {
            $stocks = $pData['stocks'];
            unset($pData['stocks']);

            $product = Product::create($pData);

            foreach ($stocks as $sData) {
                $product->stocks()->create($sData);
            }
        }
    }
}
