<?php

namespace Database\Seeders;

use App\Enums\ItemType;
use App\Enums\UserRole;
use App\Models\Item;
use App\Models\PaymentStage;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Main Vendor (CV Yulia Catering)
        $yuliaVendor = Vendor::firstOrCreate(
            ['name' => 'CV Yulia Catering'],
            [
                'contact' => '0812-3456-7890',
                'address' => 'Jl. Catering No. 10, Jakarta Timur',
                'is_active' => true,
            ]
        );

        // Link the seeded vendor user to CV Yulia Catering
        $vendorUser = User::where('username', 'vendor')->orWhere('email', 'vendor@tcprocure.test')->first();
        if ($vendorUser) {
            $vendorUser->update(['vendor_id' => $yuliaVendor->id]);
        }

        // 2. Seed Items from "daftar harga" spreadsheet
        $items = [
            ['name' => 'Snack Basah', 'unit' => 'pc', 'type' => ItemType::GOODS, 'default_price' => 3500],
            ['name' => 'Snack Kering', 'unit' => 'pc', 'type' => ItemType::GOODS, 'default_price' => 1500],
            ['name' => 'Keripik Singkong', 'unit' => 'kg', 'type' => ItemType::GOODS, 'default_price' => 70000],
            ['name' => 'Keripik Bawang', 'unit' => 'kg', 'type' => ItemType::GOODS, 'default_price' => 60000],
            ['name' => 'Ongkos Kirim', 'unit' => 'au', 'type' => ItemType::SERVICE, 'default_price' => 25000],
            ['name' => 'Gelas Kertas (isi 50)', 'unit' => 'pak', 'type' => ItemType::GOODS, 'default_price' => 25000],
            ['name' => 'Sendok Kecil Plastik (isi 100)', 'unit' => 'pak', 'type' => ItemType::GOODS, 'default_price' => 10000],
            ['name' => 'Piring Kertas (isi 10)', 'unit' => 'pak', 'type' => ItemType::GOODS, 'default_price' => 5000],
            ['name' => 'Kopi Hitam Sachet Nescafe', 'unit' => 'pc', 'type' => ItemType::GOODS, 'default_price' => 1800],
            ['name' => 'Kopi Sachet Goodday/Indocafe', 'unit' => 'pc', 'type' => ItemType::GOODS, 'default_price' => 2500],
            ['name' => 'Permen Relaxa/Kopiko/Mentos', 'unit' => 'bungkus', 'type' => ItemType::GOODS, 'default_price' => 10000],
            ['name' => 'Buavita Kotak 245mL', 'unit' => 'pc', 'type' => ItemType::GOODS, 'default_price' => 8500],
            ['name' => 'Creamer Indocafe', 'unit' => 'pc', 'type' => ItemType::GOODS, 'default_price' => 60000],
            ['name' => 'Air Mineral 330 mL', 'unit' => 'box', 'type' => ItemType::GOODS, 'default_price' => 50000],
            ['name' => 'Snack Box', 'unit' => 'box', 'type' => ItemType::GOODS, 'default_price' => 15000],
            ['name' => 'Saos Sachet', 'unit' => 'pak', 'type' => ItemType::GOODS, 'default_price' => 7500],
            ['name' => 'Nasi Box', 'unit' => 'box', 'type' => ItemType::GOODS, 'default_price' => 30000],
            ['name' => 'Air Mineral 600 mL', 'unit' => 'pc', 'type' => ItemType::GOODS, 'default_price' => 5000],
        ];

        foreach ($items as $itemData) {
            Item::firstOrCreate(
                ['name' => $itemData['name']],
                $itemData
            );
        }

        // 3. Seed Payment Stages
        $stages = [
            ['name' => 'Proses ACC', 'sort_order' => 1, 'is_final' => false],
            ['name' => 'Proses Finance', 'sort_order' => 2, 'is_final' => false],
            ['name' => 'Posted SAP', 'sort_order' => 3, 'is_final' => true],
        ];

        foreach ($stages as $stageData) {
            PaymentStage::firstOrCreate(
                ['name' => $stageData['name']],
                $stageData
            );
        }
    }
}

