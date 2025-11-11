<?php

/**
 * File: database/seeders/DatabaseSeeder.php
 * Seeder untuk data awal sistem
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Partner;
use App\Models\ProductCode;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Product Codes
        $this->seedProductCodes();

        // Seed Partners
        $partners = $this->seedPartners();

        // Seed Users
        $this->seedUsers($partners);

        $this->command->info('Database seeded successfully!');
    }

    /**
     * Seed product codes berdasarkan rancangan
     */
    private function seedProductCodes()
    {
        $productCodes = [
            // Upstream (PT Timah)
            [
                'code' => 'TIM-MON-RAW',
                'stage' => 'TIM',
                'material' => 'MON',
                'spec' => 'RAW',
                'description' => 'Mineral LTJ Mentah (Monasit)',
                'category' => 'Raw Material'
            ],

            // Middlestream (Processed)
            [
                'code' => 'MID-MON-CON',
                'stage' => 'MID',
                'material' => 'MON',
                'spec' => 'CON',
                'description' => 'Konsentrat Monasit Terolah',
                'category' => 'Concentrated'
            ],
            [
                'code' => 'MID-ND-OXI99',
                'stage' => 'MID',
                'material' => 'ND',
                'spec' => 'OXI99',
                'description' => 'Neodymium Oksida 99%',
                'category' => 'Purified Oxide'
            ],
            [
                'code' => 'MID-PR-OXI99',
                'stage' => 'MID',
                'material' => 'PR',
                'spec' => 'OXI99',
                'description' => 'Praseodymium Oksida 99%',
                'category' => 'Purified Oxide'
            ],
            [
                'code' => 'MID-CE-OXI99',
                'stage' => 'MID',
                'material' => 'CE',
                'spec' => 'OXI99',
                'description' => 'Cerium Oksida 99%',
                'category' => 'Purified Oxide'
            ],
            [
                'code' => 'MID-Y-OXI99',
                'stage' => 'MID',
                'material' => 'Y',
                'spec' => 'OXI99',
                'description' => 'Yttrium Oksida 99%',
                'category' => 'Purified Oxide'
            ],
            [
                'code' => 'MID-LE-OXI99',
                'stage' => 'MID',
                'material' => 'LE',
                'spec' => 'OXI99',
                'description' => 'Oksida LTJ Lainnya (La, Sm, dll)',
                'category' => 'Purified Oxide'
            ],
            [
                'code' => 'MID-MX-REO',
                'stage' => 'MID',
                'material' => 'MX',
                'spec' => 'REO',
                'description' => 'Mixed Rare Earth Oxide',
                'category' => 'Mixed Oxide'
            ],

            // High purity variants
            [
                'code' => 'MID-ND-OXI999',
                'stage' => 'MID',
                'material' => 'ND',
                'spec' => 'OXI999',
                'description' => 'Neodymium Oksida 99.9%+',
                'category' => 'High Purity Oxide'
            ],
            [
                'code' => 'MID-PR-OXI999',
                'stage' => 'MID',
                'material' => 'PR',
                'spec' => 'OXI999',
                'description' => 'Praseodymium Oksida 99.9%+',
                'category' => 'High Purity Oxide'
            ],

            // Metal forms
            [
                'code' => 'MID-ND-MET',
                'stage' => 'MID',
                'material' => 'ND',
                'spec' => 'MET',
                'description' => 'Neodymium Metal',
                'category' => 'Pure Metal'
            ],
            [
                'code' => 'MID-PR-MET',
                'stage' => 'MID',
                'material' => 'PR',
                'spec' => 'MET',
                'description' => 'Praseodymium Metal',
                'category' => 'Pure Metal'
            ],
        ];

        foreach ($productCodes as $code) {
            ProductCode::updateOrCreate(
                ['code' => $code['code']],
                $code
            );
        }

        $this->command->info('Product codes seeded: ' . count($productCodes));
    }

    /**
     * Seed partners
     */
    private function seedPartners()
    {
        $partners = [];

        // Middlestream Partners
        $partners['middlestream'] = Partner::updateOrCreate(
            ['name' => 'PT Pengolahan Monasit Indonesia'],
            [
                'type' => 'middlestream',
                'pic_name' => 'Budi Santoso',
                'pic_phone' => '081234567890',
                'address' => 'Kawasan Industri Cikarang, Bekasi',
                'allowed_product_codes' => ['TIM-MON-RAW', 'MID-MON-CON'],
                'status' => 'approved',
            ]
        );

        $partners['refinery'] = Partner::updateOrCreate(
            ['name' => 'PT Pemurnian LTJ Nusantara'],
            [
                'type' => 'middlestream',
                'pic_name' => 'Siti Aminah',
                'pic_phone' => '081234567891',
                'address' => 'Kawasan Industri Karawang, Jawa Barat',
                'allowed_product_codes' => [
                    'MID-MON-CON',
                    'MID-ND-OXI99',
                    'MID-PR-OXI99',
                    'MID-CE-OXI99',
                    'MID-Y-OXI99',
                    'MID-LE-OXI99'
                ],
                'status' => 'approved',
            ]
        );

        // Downstream Partners
        $partners['magnet'] = Partner::updateOrCreate(
            ['name' => 'PT Industri Magnet Permanen'],
            [
                'type' => 'downstream',
                'pic_name' => 'Ahmad Wijaya',
                'pic_phone' => '081234567892',
                'address' => 'Kawasan Industri MM2100, Cibitung',
                'allowed_product_codes' => ['MID-ND-OXI99', 'MID-PR-OXI99', 'MID-ND-MET'],
                'status' => 'approved',
            ]
        );

        $partners['phosphor'] = Partner::updateOrCreate(
            ['name' => 'PT Industri Phospor Elektronik'],
            [
                'type' => 'downstream',
                'pic_name' => 'Linda Kusuma',
                'pic_phone' => '081234567893',
                'address' => 'Kawasan Industri EJIP, Cikarang',
                'allowed_product_codes' => ['MID-PR-OXI99', 'MID-Y-OXI99'],
                'status' => 'approved',
            ]
        );

        $partners['ceramic'] = Partner::updateOrCreate(
            ['name' => 'PT Keramik Advanced Indonesia'],
            [
                'type' => 'downstream',
                'pic_name' => 'Rizki Pratama',
                'pic_phone' => '081234567894',
                'address' => 'Kawasan Industri Surabaya',
                'allowed_product_codes' => ['MID-CE-OXI99', 'MID-Y-OXI99'],
                'status' => 'approved',
            ]
        );

        $this->command->info('Partners seeded: ' . count($partners));

        return $partners;
    }

    /**
     * Seed users
     */
    private function seedUsers($partners)
    {
        // Super Admin
        User::updateOrCreate(
            ['email' => 'superadmin@timah.com'],
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'nomor_pegawai' => 'TIM-SA-001',
                'phone' => '081234560001',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // Admin PT Timah
        User::updateOrCreate(
            ['email' => 'admin@timah.com'],
            [
                'name' => 'Admin Operasional',
                'username' => 'admin.ops',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'nomor_pegawai' => 'TIM-ADM-001',
                'phone' => '081234560002',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin2@timah.com'],
            [
                'name' => 'Admin Gudang',
                'username' => 'admin.warehouse',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'nomor_pegawai' => 'TIM-ADM-002',
                'phone' => '081234560003',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // Operator PT Timah
        User::updateOrCreate(
            ['email' => 'operator1@timah.com'],
            [
                'name' => 'Operator Lapangan 1',
                'username' => 'operator1',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'nomor_pegawai' => 'TIM-OPR-001',
                'phone' => '081234560010',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'operator2@timah.com'],
            [
                'name' => 'Operator Lapangan 2',
                'username' => 'operator2',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'nomor_pegawai' => 'TIM-OPR-002',
                'phone' => '081234560011',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // Mitra Middlestream Users
        User::updateOrCreate(
            ['email' => 'mitra.monasit@partner.com'],
            [
                'name' => 'Admin Pengolahan Monasit',
                'username' => 'mitra.monasit',
                'password' => Hash::make('password'),
                'role' => 'mitra_middlestream',
                'partner_id' => $partners['middlestream']->id,
                'phone' => '081234560020',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'mitra.refinery@partner.com'],
            [
                'name' => 'Admin Pemurnian LTJ',
                'username' => 'mitra.refinery',
                'password' => Hash::make('password'),
                'role' => 'mitra_middlestream',
                'partner_id' => $partners['refinery']->id,
                'phone' => '081234560021',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // Mitra Downstream Users
        User::updateOrCreate(
            ['email' => 'downstream.magnet@partner.com'],
            [
                'name' => 'Admin Industri Magnet',
                'username' => 'down.magnet',
                'password' => Hash::make('password'),
                'role' => 'mitra_downstream',
                'partner_id' => $partners['magnet']->id,
                'phone' => '081234560030',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'downstream.phosphor@partner.com'],
            [
                'name' => 'Admin Industri Phospor',
                'username' => 'down.phosphor',
                'password' => Hash::make('password'),
                'role' => 'mitra_downstream',
                'partner_id' => $partners['phosphor']->id,
                'phone' => '081234560031',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // Auditor
        User::updateOrCreate(
            ['email' => 'auditor@timah.com'],
            [
                'name' => 'Regulator',
                'username' => 'auditor',
                'password' => Hash::make('password'),
                'role' => 'auditor',
                'nomor_pegawai' => 'TIM-AUD-001',
                'phone' => '081234560040',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'auditor.ext@external.com'],
            [
                'name' => 'Auditor Eksternal',
                'username' => 'auditor.external',
                'password' => Hash::make('password'),
                'role' => 'auditor',
                'phone' => '081234560041',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        $this->command->info('Users seeded successfully!');
        $this->command->info('');
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('Super Admin: superadmin@timah.com / password');
        $this->command->info('Admin: admin@timah.com / password');
        $this->command->info('Operator: operator1@timah.com / password');
        $this->command->info('Mitra Middlestream: mitra.refinery@partner.com / password');
        $this->command->info('Mitra Downstream: downstream.magnet@partner.com / password');
        $this->command->info('Auditor: auditor@timah.com / password');
        $this->command->info('========================');
    }
}