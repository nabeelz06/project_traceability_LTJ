<?php

/**
 * File: database/seeders/DatabaseSeeder.php
 * Seeder untuk data awal sistem - Updated dengan data perusahaan aktual
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
     * Seed partners berdasarkan data aktual
     */
    private function seedPartners()
    {
        $partners = [];

        // ==========================================
        // UPSTREAM PARTNERS - Tambang Timah
        // ==========================================
        
        $partners['timah'] = Partner::updateOrCreate(
            ['name' => 'PT Timah Tbk'],
            [
                'type' => 'upstream',
                'pic_name' => 'Manager Produksi PT Timah',
                'pic_phone' => '021-12345001',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['TIM-MON-RAW'],
                'status' => 'approved',
            ]
        );

        $partners['antam'] = Partner::updateOrCreate(
            ['name' => 'PT Aneka Tambang Tbk'],
            [
                'type' => 'upstream',
                'pic_name' => 'Manager Operasional Antam',
                'pic_phone' => '021-12345002',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['TIM-MON-RAW'],
                'status' => 'approved',
            ]
        );

        $partners['mitra_stania'] = Partner::updateOrCreate(
            ['name' => 'PT Mitra Stania Prima'],
            [
                'type' => 'upstream',
                'pic_name' => 'Direktur Operasional',
                'pic_phone' => '021-12345003',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['TIM-MON-RAW'],
                'status' => 'approved',
            ]
        );

        $partners['aega'] = Partner::updateOrCreate(
            ['name' => 'PT AEGA Prima'],
            [
                'type' => 'upstream',
                'pic_name' => 'Manager Produksi',
                'pic_phone' => '021-12345004',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['TIM-MON-RAW'],
                'status' => 'approved',
            ]
        );

        // ==========================================
        // MIDDLESTREAM PARTNERS - Pengolahan Monazite
        // (Catatan: Belum ada di Indonesia, data sebagai contoh/simulasi)
        // ==========================================
        
        $partners['middlestream_processing'] = Partner::updateOrCreate(
            ['name' => 'PT Pengolahan Monasit Indonesia (Simulasi)'],
            [
                'type' => 'middlestream',
                'pic_name' => 'Budi Santoso',
                'pic_phone' => '081234567890',
                'address' => 'Kawasan Industri Cikarang, Bekasi',
                'allowed_product_codes' => ['TIM-MON-RAW', 'MID-MON-CON'],
                'status' => 'pending', // Pending karena belum beroperasi
            ]
        );

        $partners['refinery'] = Partner::updateOrCreate(
            ['name' => 'PT Pemurnian LTJ Nusantara (Simulasi)'],
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
                'status' => 'pending', // Pending karena belum beroperasi
            ]
        );

        // ==========================================
        // DOWNSTREAM PARTNERS - Advanced Materials
        // (Catatan: Belum ada di Indonesia untuk magnet permanen, baterai LTJ, dll)
        // ==========================================
        
        $partners['magnet_indo'] = Partner::updateOrCreate(
            ['name' => 'PT Industri Magnet Permanen Indonesia (Simulasi)'],
            [
                'type' => 'downstream',
                'pic_name' => 'Ahmad Wijaya',
                'pic_phone' => '081234567892',
                'address' => 'Kawasan Industri MM2100, Cibitung',
                'allowed_product_codes' => ['MID-ND-OXI99', 'MID-PR-OXI99', 'MID-ND-MET'],
                'status' => 'pending',
            ]
        );

        $partners['battery'] = Partner::updateOrCreate(
            ['name' => 'PT Industri Baterai Advanced (Simulasi)'],
            [
                'type' => 'downstream',
                'pic_name' => 'Dewi Kusuma',
                'pic_phone' => '081234567893',
                'address' => 'Kawasan Industri Karawang',
                'allowed_product_codes' => ['MID-ND-OXI99', 'MID-CE-OXI99'],
                'status' => 'pending',
            ]
        );

        $partners['catalyst'] = Partner::updateOrCreate(
            ['name' => 'PT Industri Katalis Indonesia (Simulasi)'],
            [
                'type' => 'downstream',
                'pic_name' => 'Rudi Hermawan',
                'pic_phone' => '081234567894',
                'address' => 'Kawasan Industri Cilegon',
                'allowed_product_codes' => ['MID-CE-OXI99', 'MID-LE-OXI99'],
                'status' => 'pending',
            ]
        );

        $partners['phosphor'] = Partner::updateOrCreate(
            ['name' => 'PT Industri Phosphor Elektronik (Simulasi)'],
            [
                'type' => 'downstream',
                'pic_name' => 'Linda Kusuma',
                'pic_phone' => '081234567895',
                'address' => 'Kawasan Industri EJIP, Cikarang',
                'allowed_product_codes' => ['MID-PR-OXI99', 'MID-Y-OXI99'],
                'status' => 'pending',
            ]
        );

        $partners['fuelcell'] = Partner::updateOrCreate(
            ['name' => 'PT Industri Fuel Cell Indonesia (Simulasi)'],
            [
                'type' => 'downstream',
                'pic_name' => 'Bambang Suryanto',
                'pic_phone' => '081234567896',
                'address' => 'Kawasan Industri Surabaya',
                'allowed_product_codes' => ['MID-Y-OXI99', 'MID-CE-OXI99'],
                'status' => 'pending',
            ]
        );

        // ==========================================
        // END USER PARTNERS - Kilang & Petrochemical
        // ==========================================
        
        $partners['pertamina'] = Partner::updateOrCreate(
            ['name' => 'PT Pertamina (Persero)'],
            [
                'type' => 'end_user',
                'pic_name' => 'Manager Procurement Pertamina',
                'pic_phone' => '021-13456001',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['MID-CE-OXI99', 'MID-LE-OXI99'], // Untuk katalis
                'status' => 'approved',
            ]
        );

        $partners['pertamina_rc'] = Partner::updateOrCreate(
            ['name' => 'Pertamina Research Center'],
            [
                'type' => 'end_user',
                'pic_name' => 'Kepala Riset',
                'pic_phone' => '021-13456002',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['MID-CE-OXI99', 'MID-ND-OXI99'],
                'status' => 'approved',
            ]
        );

        $partners['rekacipta'] = Partner::updateOrCreate(
            ['name' => 'PT Rekacipta Inovasi (ITB spin-off)'],
            [
                'type' => 'end_user',
                'pic_name' => 'Direktur Riset',
                'pic_phone' => '022-13456003',
                'address' => 'Bandung',
                'allowed_product_codes' => ['MID-CE-OXI99', 'MID-ND-OXI99', 'MID-Y-OXI99'],
                'status' => 'approved',
            ]
        );

        $partners['pupuk_kujang'] = Partner::updateOrCreate(
            ['name' => 'PT Pupuk Kujang'],
            [
                'type' => 'end_user',
                'pic_name' => 'Manager Produksi',
                'pic_phone' => '021-13456004',
                'address' => 'Cikampek, Jawa Barat',
                'allowed_product_codes' => ['MID-CE-OXI99'],
                'status' => 'approved',
            ]
        );

        $partners['chandra_asri'] = Partner::updateOrCreate(
            ['name' => 'PT Chandra Asri Petrochemical'],
            [
                'type' => 'end_user',
                'pic_name' => 'Procurement Manager',
                'pic_phone' => '021-13456005',
                'address' => 'Cilegon, Banten',
                'allowed_product_codes' => ['MID-CE-OXI99', 'MID-LE-OXI99'],
                'status' => 'approved',
            ]
        );

        // ==========================================
        // END USER PARTNERS - Automotive
        // ==========================================
        
        $partners['denso'] = Partner::updateOrCreate(
            ['name' => 'Denso Indonesia'],
            [
                'type' => 'end_user',
                'pic_name' => 'Purchasing Manager',
                'pic_phone' => '021-13457001',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['MID-ND-OXI99', 'MID-PR-OXI99', 'MID-CE-OXI99'], // Untuk sensor, katalis
                'status' => 'approved',
            ]
        );

        $partners['astra_otoparts'] = Partner::updateOrCreate(
            ['name' => 'PT Astra Otoparts Tbk'],
            [
                'type' => 'end_user',
                'pic_name' => 'Direktur Procurement',
                'pic_phone' => '021-13457002',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['MID-ND-OXI99', 'MID-CE-OXI99'],
                'status' => 'approved',
            ]
        );

        $partners['ngk_busi'] = Partner::updateOrCreate(
            ['name' => 'NGK Busi Indonesia'],
            [
                'type' => 'end_user',
                'pic_name' => 'Production Manager',
                'pic_phone' => '021-13457003',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['MID-Y-OXI99', 'MID-CE-OXI99'],
                'status' => 'approved',
            ]
        );

        // ==========================================
        // END USER PARTNERS - Glass & Optical
        // ==========================================
        
        $partners['kenertec'] = Partner::updateOrCreate(
            ['name' => 'PT Kenertec Power System'],
            [
                'type' => 'end_user',
                'pic_name' => 'Engineering Manager',
                'pic_phone' => '021-13458001',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['MID-Y-OXI99', 'MID-ND-OXI99'],
                'status' => 'approved',
            ]
        );

        $partners['pln'] = Partner::updateOrCreate(
            ['name' => 'PT PLN (Persero)'],
            [
                'type' => 'end_user',
                'pic_name' => 'Manager Procurement',
                'pic_phone' => '021-13458002',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['MID-Y-OXI99', 'MID-ND-OXI99'],
                'status' => 'approved',
            ]
        );

        $partners['len_industri'] = Partner::updateOrCreate(
            ['name' => 'PT Len Industri (Defend ID)'],
            [
                'type' => 'end_user',
                'pic_name' => 'Direktur Operasional',
                'pic_phone' => '022-13458003',
                'address' => 'Bandung',
                'allowed_product_codes' => ['MID-ND-OXI99', 'MID-Y-OXI99', 'MID-PR-OXI99'],
                'status' => 'approved',
            ]
        );

        $partners['siemens'] = Partner::updateOrCreate(
            ['name' => 'PT Siemens Indonesia'],
            [
                'type' => 'end_user',
                'pic_name' => 'Procurement Director',
                'pic_phone' => '021-13458004',
                'address' => 'Jakarta',
                'allowed_product_codes' => ['MID-ND-OXI99', 'MID-Y-OXI99'],
                'status' => 'approved',
            ]
        );

        // ==========================================
        // END USER PARTNERS - Aerospace & Defense
        // ==========================================
        
        $partners['dirgantara'] = Partner::updateOrCreate(
            ['name' => 'PT Dirgantara Indonesia (PTDI)'],
            [
                'type' => 'end_user',
                'pic_name' => 'VP Procurement',
                'pic_phone' => '022-13459001',
                'address' => 'Bandung',
                'allowed_product_codes' => ['MID-ND-OXI99', 'MID-PR-OXI99', 'MID-Y-OXI99', 'MID-CE-OXI99'],
                'status' => 'approved',
            ]
        );

        $partners['pindad'] = Partner::updateOrCreate(
            ['name' => 'PT Pindad (Persero)'],
            [
                'type' => 'end_user',
                'pic_name' => 'Direktur Produksi',
                'pic_phone' => '022-13459002',
                'address' => 'Bandung',
                'allowed_product_codes' => ['MID-ND-OXI99', 'MID-PR-OXI99', 'MID-Y-OXI99'],
                'status' => 'approved',
            ]
        );

        $partners['pal'] = Partner::updateOrCreate(
            ['name' => 'PT PAL Indonesia'],
            [
                'type' => 'end_user',
                'pic_name' => 'Direktur Teknik',
                'pic_phone' => '031-13459003',
                'address' => 'Surabaya',
                'allowed_product_codes' => ['MID-ND-OXI99', 'MID-Y-OXI99', 'MID-CE-OXI99'],
                'status' => 'approved',
            ]
        );

        $this->command->info('Partners seeded: ' . count($partners));

        return $partners;
    }

    /**
     * Seed users untuk berbagai partners
     */
    private function seedUsers($partners)
    {
        // ==========================================
        // SUPER ADMIN & ADMIN PT TIMAH
        // ==========================================
        
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
            ['email' => 'admin.warehouse@timah.com'],
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

        // ==========================================
        // OPERATOR PT TIMAH
        // ==========================================
        
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

        // ==========================================
        // UPSTREAM USERS
        // ==========================================
        
        User::updateOrCreate(
            ['email' => 'user@antam.com'],
            [
                'name' => 'User PT Antam',
                'username' => 'user.antam',
                'password' => Hash::make('password'),
                'role' => 'mitra_upstream',
                'partner_id' => $partners['antam']->id,
                'phone' => '081234560020',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@mitrastania.com'],
            [
                'name' => 'User PT Mitra Stania Prima',
                'username' => 'user.stania',
                'password' => Hash::make('password'),
                'role' => 'mitra_upstream',
                'partner_id' => $partners['mitra_stania']->id,
                'phone' => '081234560021',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // ==========================================
        // MIDDLESTREAM USERS (Simulasi)
        // ==========================================
        
        User::updateOrCreate(
            ['email' => 'mitra.processing@partner.com'],
            [
                'name' => 'Admin Pengolahan Monasit',
                'username' => 'mitra.processing',
                'password' => Hash::make('password'),
                'role' => 'mitra_middlestream',
                'partner_id' => $partners['middlestream_processing']->id,
                'phone' => '081234560030',
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
                'phone' => '081234560031',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // ==========================================
        // DOWNSTREAM USERS (Simulasi)
        // ==========================================
        
        User::updateOrCreate(
            ['email' => 'downstream.magnet@partner.com'],
            [
                'name' => 'Admin Industri Magnet',
                'username' => 'down.magnet',
                'password' => Hash::make('password'),
                'role' => 'mitra_downstream',
                'partner_id' => $partners['magnet_indo']->id,
                'phone' => '081234560040',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'downstream.battery@partner.com'],
            [
                'name' => 'Admin Industri Baterai',
                'username' => 'down.battery',
                'password' => Hash::make('password'),
                'role' => 'mitra_downstream',
                'partner_id' => $partners['battery']->id,
                'phone' => '081234560041',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'downstream.phosphor@partner.com'],
            [
                'name' => 'Admin Industri Phosphor',
                'username' => 'down.phosphor',
                'password' => Hash::make('password'),
                'role' => 'mitra_downstream',
                'partner_id' => $partners['phosphor']->id,
                'phone' => '081234560042',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // ==========================================
        // END USER - Petrochemical
        // ==========================================
        
        User::updateOrCreate(
            ['email' => 'user@pertamina.com'],
            [
                'name' => 'User PT Pertamina',
                'username' => 'user.pertamina',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['pertamina']->id,
                'phone' => '081234560050',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@rekacipta.com'],
            [
                'name' => 'User PT Rekacipta Inovasi',
                'username' => 'user.rekacipta',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['rekacipta']->id,
                'phone' => '081234560051',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@chandrаasri.com'],
            [
                'name' => 'User PT Chandra Asri Petrochemical',
                'username' => 'user.chandrаasri',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['chandra_asri']->id,
                'phone' => '081234560052',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // ==========================================
        // END USER - Automotive
        // ==========================================
        
        User::updateOrCreate(
            ['email' => 'user@denso.co.id'],
            [
                'name' => 'User Denso Indonesia',
                'username' => 'user.denso',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['denso']->id,
                'phone' => '081234560060',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@astra-otoparts.com'],
            [
                'name' => 'User PT Astra Otoparts',
                'username' => 'user.astra',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['astra_otoparts']->id,
                'phone' => '081234560061',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@ngkbusi.co.id'],
            [
                'name' => 'User NGK Busi Indonesia',
                'username' => 'user.ngk',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['ngk_busi']->id,
                'phone' => '081234560062',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // ==========================================
        // END USER - Glass & Optical / Energy
        // ==========================================
        
        User::updateOrCreate(
            ['email' => 'user@pln.co.id'],
            [
                'name' => 'User PT PLN',
                'username' => 'user.pln',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['pln']->id,
                'phone' => '081234560070',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@len.co.id'],
            [
                'name' => 'User PT Len Industri',
                'username' => 'user.len',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['len_industri']->id,
                'phone' => '081234560071',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@siemens.com'],
            [
                'name' => 'User PT Siemens Indonesia',
                'username' => 'user.siemens',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['siemens']->id,
                'phone' => '081234560072',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // ==========================================
        // END USER - Aerospace & Defense
        // ==========================================
        
        User::updateOrCreate(
            ['email' => 'user@indonesian-aerospace.com'],
            [
                'name' => 'User PT Dirgantara Indonesia',
                'username' => 'user.ptdi',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['dirgantara']->id,
                'phone' => '081234560080',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@pindad.com'],
            [
                'name' => 'User PT Pindad',
                'username' => 'user.pindad',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['pindad']->id,
                'phone' => '081234560081',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'user@pal.co.id'],
            [
                'name' => 'User PT PAL Indonesia',
                'username' => 'user.pal',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['pal']->id,
                'phone' => '081234560082',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        // ==========================================
        // AUDITOR / REGULATOR
        // ==========================================
        
        User::updateOrCreate(
            ['email' => 'auditor@timah.com'],
            [
                'name' => 'Regulator Internal',
                'username' => 'auditor',
                'password' => Hash::make('password'),
                'role' => 'auditor',
                'nomor_pegawai' => 'TIM-AUD-001',
                'phone' => '081234560090',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'auditor.ext@kemenristekdikti.go.id'],
            [
                'name' => 'Auditor Kemenristekdikti',
                'username' => 'auditor.external',
                'password' => Hash::make('password'),
                'role' => 'auditor',
                'phone' => '081234560091',
                'is_active' => true,
                'enable_2fa' => false,
            ]
        );

        $this->command->info('Users seeded successfully!');
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('PT TIMAH (Internal):');
        $this->command->info('  Super Admin    : superadmin@timah.com / password');
        $this->command->info('  Admin Ops      : admin@timah.com / password');
        $this->command->info('  Admin Warehouse: admin.warehouse@timah.com / password');
        $this->command->info('  Operator       : operator1@timah.com / password');
        $this->command->info('');
        $this->command->info('UPSTREAM PARTNERS:');
        $this->command->info('  PT Antam       : user@antam.com / password');
        $this->command->info('  PT Mitra Stania: user@mitrastania.com / password');
        $this->command->info('');
        $this->command->info('MIDDLESTREAM PARTNERS (Simulasi):');
        $this->command->info('  Processing     : mitra.processing@partner.com / password');
        $this->command->info('  Refinery       : mitra.refinery@partner.com / password');
        $this->command->info('');
        $this->command->info('DOWNSTREAM PARTNERS (Simulasi):');
        $this->command->info('  Magnet         : downstream.magnet@partner.com / password');
        $this->command->info('  Battery        : downstream.battery@partner.com / password');
        $this->command->info('  Phosphor       : downstream.phosphor@partner.com / password');
        $this->command->info('');
        $this->command->info('END USERS - Petrochemical:');
        $this->command->info('  Pertamina      : user@pertamina.com / password');
        $this->command->info('  Rekacipta      : user@rekacipta.com / password');
        $this->command->info('  Chandra Asri   : user@chandrаasri.com / password');
        $this->command->info('');
        $this->command->info('END USERS - Automotive:');
        $this->command->info('  Denso          : user@denso.co.id / password');
        $this->command->info('  Astra Otoparts : user@astra-otoparts.com / password');
        $this->command->info('  NGK Busi       : user@ngkbusi.co.id / password');
        $this->command->info('');
        $this->command->info('END USERS - Energy/Optical:');
        $this->command->info('  PLN            : user@pln.co.id / password');
        $this->command->info('  Len Industri   : user@len.co.id / password');
        $this->command->info('  Siemens        : user@siemens.com / password');
        $this->command->info('');
        $this->command->info('END USERS - Aerospace & Defense:');
        $this->command->info('  PT Dirgantara  : user@indonesian-aerospace.com / password');
        $this->command->info('  PT Pindad      : user@pindad.com / password');
        $this->command->info('  PT PAL         : user@pal.co.id / password');
        $this->command->info('');
        $this->command->info('AUDITOR/REGULATOR:');
        $this->command->info('  Internal       : auditor@timah.com / password');
        $this->command->info('  Kemenristekdikti: auditor.ext@kemenristekdikti.go.id / password');
        $this->command->info('========================================');
    }
}