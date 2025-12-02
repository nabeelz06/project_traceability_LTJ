<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Partner;
use App\Models\ProductCode;
use App\Models\Batch;
use App\Models\BatchLog;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed database sistem traceability LTJ PT Timah
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting database seeding for LTJ Traceability System...');
        $this->command->info('📅 Date: ' . now()->format('d F Y H:i:s'));
        $this->command->info('');

        // 1. Seed Product Codes
        $this->seedProductCodes();

        // 2. Seed Partners
        $partners = $this->seedPartners();

        // 3. Seed Users
        $users = $this->seedUsers($partners);

        // 4. Seed 7 Batches dengan ~50 activity logs
        $this->seed7BatchesWithDetailedFlow($users, $partners);

        $this->command->info('');
        $this->command->info('✅ Database seeded successfully!');
        $this->printSummary();
    }

    /**
     * Seed Product Codes
     */
    private function seedProductCodes()
    {
        $this->command->info('📦 Seeding product codes...');

        $productCodes = [
            // UPSTREAM - Raw Material dari PT Timah
            [
                'code' => 'TIM-MON-RAW',
                'stage' => 'Upstream',
                'material' => 'MON',
                'spec' => 'RAW',
                'description' => 'Monasit Mentah dari Mineral Ikutan Timah',
                'category' => 'Raw Material',
            ],

            // MIDSTREAM - Konsentrat
            [
                'code' => 'MID-MON-CON',
                'stage' => 'Midstream',
                'material' => 'MON',
                'spec' => 'CON',
                'description' => 'Konsentrat Monasit Terolah',
                'category' => 'Concentrated',
            ],

            // MIDSTREAM - Purified Oxides
            [
                'code' => 'MID-ND-OXI99',
                'stage' => 'Midstream',
                'material' => 'ND',
                'spec' => 'OXI99',
                'description' => 'Neodymium (Nd) Oksida 99% - untuk magnet permanen',
                'category' => 'Purified Oxide',
            ],
            [
                'code' => 'MID-CE-OXI99',
                'stage' => 'Midstream',
                'material' => 'CE',
                'spec' => 'OXI99',
                'description' => 'Cerium (Ce) Oksida 99% - untuk katalis & polishing',
                'category' => 'Purified Oxide',
            ],
            [
                'code' => 'MID-Y-OXI99',
                'stage' => 'Midstream',
                'material' => 'Y',
                'spec' => 'OXI99',
                'description' => 'Yttrium (Y) Oksida 99% - untuk phosphor & keramik',
                'category' => 'Purified Oxide',
            ],
            [
                'code' => 'MID-PR-OXI99',
                'stage' => 'Midstream',
                'material' => 'PR',
                'spec' => 'OXI99',
                'description' => 'Praseodymium (Pr) Oksida 99% - untuk magnet & keramik',
                'category' => 'Purified Oxide',
            ],
            [
                'code' => 'MID-LA-OXI99',
                'stage' => 'Midstream',
                'material' => 'LA',
                'spec' => 'OXI99',
                'description' => 'Lanthanum (La) Oksida 99% - untuk katalis',
                'category' => 'Purified Oxide',
            ],
        ];

        foreach ($productCodes as $code) {
            ProductCode::updateOrCreate(
                ['code' => $code['code']],
                $code
            );
        }

        $this->command->info('   ✓ Product codes: 7 kode produk');
    }

    /**
     * Seed Partners
     */
    private function seedPartners()
    {
        $this->command->info('🏢 Seeding partners...');
        
        $partners = [];

        // UPSTREAM
        $partners['timah'] = Partner::updateOrCreate(
            ['name' => 'PT Timah Tbk'],
            [
                'type' => 'upstream',
                'pic_name' => 'Direktur Operasional PT Timah',
                'pic_phone' => '021-5063800',
                'address' => 'Jl. Jend. Sudirman Kav. 36, Jakarta Selatan',
                'allowed_product_codes' => json_encode(['TIM-MON-RAW']),
                'status' => 'approved',
            ]
        );

        $partners['antam'] = Partner::updateOrCreate(
            ['name' => 'PT Aneka Tambang Tbk'],
            [
                'type' => 'upstream',
                'pic_name' => 'Manager Produksi Antam',
                'pic_phone' => '021-2924477',
                'address' => 'Jl. T.B. Simatupang No. 1, Jakarta Selatan',
                'allowed_product_codes' => json_encode(['TIM-MON-RAW']),
                'status' => 'approved',
            ]
        );

        $partners['mitra_stania'] = Partner::updateOrCreate(
            ['name' => 'PT Mitra Stania Prima'],
            [
                'type' => 'upstream',
                'pic_name' => 'Direktur Operasional',
                'pic_phone' => '021-5551234',
                'address' => 'Jakarta',
                'allowed_product_codes' => json_encode(['TIM-MON-RAW']),
                'status' => 'approved',
            ]
        );

        $partners['aega'] = Partner::updateOrCreate(
            ['name' => 'PT AEGA Prima'],
            [
                'type' => 'upstream',
                'pic_name' => 'Manager Produksi',
                'pic_phone' => '021-5556789',
                'address' => 'Jakarta',
                'allowed_product_codes' => json_encode(['TIM-MON-RAW']),
                'status' => 'approved',
            ]
        );

        // END USER
        $partners['pertamina'] = Partner::updateOrCreate(
            ['name' => 'PT Pertamina (Persero)'],
            [
                'type' => 'end_user',
                'pic_name' => 'Manager Procurement',
                'pic_phone' => '021-3815111',
                'address' => 'Jl. Medan Merdeka Timur 1A, Jakarta Pusat',
                'allowed_product_codes' => json_encode(['MID-CE-OXI99']),
                'status' => 'approved',
            ]
        );

        $partners['rekacipta'] = Partner::updateOrCreate(
            ['name' => 'PT Rekacipta Inovasi (ITB spin-off)'],
            [
                'type' => 'end_user',
                'pic_name' => 'Direktur Riset & Inovasi',
                'pic_phone' => '022-2534567',
                'address' => 'Bandung, Jawa Barat',
                'allowed_product_codes' => json_encode(['MID-ND-OXI99', 'MID-Y-OXI99']),
                'status' => 'approved',
            ]
        );

        $partners['pupuk_kujang'] = Partner::updateOrCreate(
            ['name' => 'PT Pupuk Kujang'],
            [
                'type' => 'end_user',
                'pic_name' => 'Manager Produksi',
                'pic_phone' => '0264-123456',
                'address' => 'Cikampek, Jawa Barat',
                'allowed_product_codes' => json_encode(['MID-CE-OXI99']),
                'status' => 'approved',
            ]
        );

        $partners['len'] = Partner::updateOrCreate(
            ['name' => 'PT Len Industri (Defend ID)'],
            [
                'type' => 'end_user',
                'pic_name' => 'Direktur Operasional',
                'pic_phone' => '022-6032777',
                'address' => 'Bandung, Jawa Barat',
                'allowed_product_codes' => json_encode(['MID-ND-OXI99']),
                'status' => 'approved',
            ]
        );

        $partners['denso'] = Partner::updateOrCreate(
            ['name' => 'Denso Indonesia'],
            [
                'type' => 'end_user',
                'pic_name' => 'Purchasing Manager',
                'pic_phone' => '021-89831000',
                'address' => 'Jakarta',
                'allowed_product_codes' => json_encode(['MID-ND-OXI99']),
                'status' => 'approved',
            ]
        );

        $partners['astra'] = Partner::updateOrCreate(
            ['name' => 'PT Astra Otoparts Tbk'],
            [
                'type' => 'end_user',
                'pic_name' => 'Direktur Procurement',
                'pic_phone' => '021-6519555',
                'address' => 'Jakarta',
                'allowed_product_codes' => json_encode(['MID-ND-OXI99']),
                'status' => 'approved',
            ]
        );

        $partners['ngk'] = Partner::updateOrCreate(
            ['name' => 'NGK Busi Indonesia'],
            [
                'type' => 'end_user',
                'pic_name' => 'Production Manager',
                'pic_phone' => '021-5904567',
                'address' => 'Jakarta',
                'allowed_product_codes' => json_encode(['MID-Y-OXI99']),
                'status' => 'approved',
            ]
        );

        $partners['pindad'] = Partner::updateOrCreate(
            ['name' => 'PT Pindad (Persero)'],
            [
                'type' => 'end_user',
                'pic_name' => 'Direktur Produksi',
                'pic_phone' => '022-7304112',
                'address' => 'Bandung, Jawa Barat',
                'allowed_product_codes' => json_encode(['MID-ND-OXI99']),
                'status' => 'approved',
            ]
        );

        $partners['dirgantara'] = Partner::updateOrCreate(
            ['name' => 'PT Dirgantara Indonesia (PTDI)'],
            [
                'type' => 'end_user',
                'pic_name' => 'VP Procurement',
                'pic_phone' => '022-6034111',
                'address' => 'Bandung, Jawa Barat',
                'allowed_product_codes' => json_encode(['MID-ND-OXI99']),
                'status' => 'approved',
            ]
        );

        $partners['siemens'] = Partner::updateOrCreate(
            ['name' => 'PT Siemens Indonesia (Energy Solutions)'],
            [
                'type' => 'end_user',
                'pic_name' => 'Procurement Director',
                'pic_phone' => '021-57951888',
                'address' => 'Jakarta',
                'allowed_product_codes' => json_encode(['MID-Y-OXI99']),
                'status' => 'approved',
            ]
        );

        $this->command->info('   ✓ Partners: ' . count($partners) . ' companies');

        return $partners;
    }

    /**
     * Seed Users
     */
    private function seedUsers($partners)
    {
        $this->command->info('👥 Seeding users...');
        
        $users = [];

        $users['superadmin'] = User::updateOrCreate(
            ['email' => 'superadmin@timah.com'],
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'partner_id' => null,
                'phone' => '081234560001',
                'is_active' => true,
            ]
        );

        $users['admin'] = User::updateOrCreate(
            ['email' => 'admin@timah.com'],
            [
                'name' => 'Admin Operasional PT Timah',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'partner_id' => null,
                'phone' => '081234560002',
                'is_active' => true,
            ]
        );

        $users['operator_timah'] = User::updateOrCreate(
            ['email' => 'operator@timah.com'],
            [
                'name' => 'Operator Lapangan PT Timah',
                'username' => 'operator.timah',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'partner_id' => null,
                'phone' => '081234560010',
                'is_active' => true,
            ]
        );

        $users['user_rekacipta'] = User::updateOrCreate(
            ['email' => 'user@rekacipta.com'],
            [
                'name' => 'User PT Rekacipta Inovasi',
                'username' => 'user.rekacipta',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['rekacipta']->id,
                'phone' => '081234560050',
                'is_active' => true,
            ]
        );

        $users['user_len'] = User::updateOrCreate(
            ['email' => 'user@len.co.id'],
            [
                'name' => 'User PT Len Industri',
                'username' => 'user.len',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['len']->id,
                'phone' => '081234560051',
                'is_active' => true,
            ]
        );

        $users['user_pupuk'] = User::updateOrCreate(
            ['email' => 'user@pupukkujang.com'],
            [
                'name' => 'User PT Pupuk Kujang',
                'username' => 'user.pupuk',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['pupuk_kujang']->id,
                'phone' => '081234560052',
                'is_active' => true,
            ]
        );

        $users['user_pindad'] = User::updateOrCreate(
            ['email' => 'user@pindad.com'],
            [
                'name' => 'User PT Pindad',
                'username' => 'user.pindad',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['pindad']->id,
                'phone' => '081234560053',
                'is_active' => true,
            ]
        );

        $users['user_dirgantara'] = User::updateOrCreate(
            ['email' => 'user@indonesian-aerospace.com'],
            [
                'name' => 'User PT Dirgantara Indonesia',
                'username' => 'user.ptdi',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['dirgantara']->id,
                'phone' => '081234560054',
                'is_active' => true,
            ]
        );

        $users['user_denso'] = User::updateOrCreate(
            ['email' => 'user@denso.co.id'],
            [
                'name' => 'User Denso Indonesia',
                'username' => 'user.denso',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['denso']->id,
                'phone' => '081234560055',
                'is_active' => true,
            ]
        );

        $users['user_astra'] = User::updateOrCreate(
            ['email' => 'user@astraotoparts.com'],
            [
                'name' => 'User PT Astra Otoparts',
                'username' => 'user.astra',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['astra']->id,
                'phone' => '081234560056',
                'is_active' => true,
            ]
        );

        $users['user_ngk'] = User::updateOrCreate(
            ['email' => 'user@ngkbusi.co.id'],
            [
                'name' => 'User NGK Busi Indonesia',
                'username' => 'user.ngk',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['ngk']->id,
                'phone' => '081234560057',
                'is_active' => true,
            ]
        );

        $users['user_siemens'] = User::updateOrCreate(
            ['email' => 'user@siemens.com'],
            [
                'name' => 'User PT Siemens Indonesia',
                'username' => 'user.siemens',
                'password' => Hash::make('password'),
                'role' => 'end_user',
                'partner_id' => $partners['siemens']->id,
                'phone' => '081234560058',
                'is_active' => true,
            ]
        );

        $users['auditor'] = User::updateOrCreate(
            ['email' => 'auditor@timah.com'],
            [
                'name' => 'Auditor Internal PT Timah',
                'username' => 'auditor',
                'password' => Hash::make('password'),
                'role' => 'auditor',
                'partner_id' => null,
                'phone' => '081234560090',
                'is_active' => true,
            ]
        );

        $this->command->info('   ✓ Users: ' . count($users) . ' users');

        return $users;
    }

    /**
     * Seed 7 Batches dengan detailed flow
     */
    private function seed7BatchesWithDetailedFlow($users, $partners)
    {
        $this->command->info('📦 Seeding 7 batches with detailed flow...');

        // Base timestamp
        $baseDate = Carbon::create(2025, 11, 20, 8, 0, 0);

        // Array untuk activities
        $allActivities = [];

        // ======================================
        // BATCH 1: Flow Lengkap
        // ======================================
        $batch1 = Batch::create([
            'batch_code' => 'TIM-MON-20251120-001',
            'lot_number' => 'L-MON-20251120-001-A',
            
            // PERBAIKAN DISINI: Ganti 'product_code' menjadi 'product_code_id'
            'product_code_id' => 1, // Mengacu pada ID TIM-MON-RAW
            
            'status' => 'delivered',
            'initial_weight' => 20000,
            'current_weight' => 2400,
            'weight_unit' => 'kg',
            'tonase' => 20.00,
            'konsentrat_persen' => 15.00,
            'massa_ltj_kg' => 3000,
            'nd_content' => 40.00, 'y_content' => 30.00, 'ce_content' => 15.00, 'la_content' => 10.00, 'pr_content' => 5.00,
            'origin_location' => 'PT Timah Bangka',
            'current_location' => 'PT Dirgantara Indonesia (PTDI)',
            'container_code' => 'K-TMH-001',
            'keterangan' => 'Diterima dari PT Timah. Input 3000 kg LTJ',
            'created_by' => $users['user_rekacipta']->id,
            'current_owner_partner_id' => $partners['dirgantara']->id,
            'is_ready' => true,
        ]);

        // ... (Log aktivitas Batch 1 biarkan tetap sama)

        // ======================================
        // BATCH 2: Flow dengan Export Track
        // ======================================
        $batch2 = Batch::create([
            'batch_code' => 'TIM-MON-20251120-002',
            'lot_number' => 'L-MON-20251120-002-B',
            
            // PERBAIKAN DISINI
            'product_code_id' => 1, // TIM-MON-RAW
            
            'status' => 'delivered',
            'initial_weight' => 30000,
            'current_weight' => 1500,
            'weight_unit' => 'kg',
            'tonase' => 30.00,
            'konsentrat_persen' => 10.00,
            'massa_ltj_kg' => 3000,
            'nd_content' => 35.00, 'y_content' => 25.00, 'ce_content' => 20.00, 'la_content' => 15.00, 'pr_content' => 5.00,
            'origin_location' => 'PT Len Industri (Defend ID)',
            'current_location' => 'NGK Busi Indonesia',
            'container_code' => 'K-TMH-002',
            'keterangan' => 'Diterima 1.5T material LTJ oleh NGK Busi',
            'created_by' => $users['user_len']->id,
            'current_owner_partner_id' => $partners['ngk']->id,
            'is_ready' => true,
        ]);

        // ... (Log aktivitas Batch 2 tetap sama)

        // ======================================
        // BATCH 3: Flow Kompleks
        // ======================================
        $batch3 = Batch::create([
            'batch_code' => 'TIM-MON-20251120-003',
            'lot_number' => 'L-MON-20251120-003-C',
            
            // PERBAIKAN DISINI
            'product_code_id' => 1, // TIM-MON-RAW
            
            'status' => 'processing',
            'initial_weight' => 50000,
            'current_weight' => 50000,
            'weight_unit' => 'kg',
            'tonase' => 50.00,
            'konsentrat_persen' => 8.00,
            'massa_ltj_kg' => 4000,
            'nd_content' => 38.00, 'y_content' => 22.00, 'ce_content' => 18.00, 'la_content' => 12.00, 'pr_content' => 10.00,
            'origin_location' => 'PT Aneka Tambang Tbk',
            'current_location' => 'Processing - PT Aneka Tambang',
            'container_code' => 'K-TMH-003',
            'keterangan' => 'Dalam proses pemisahan - Batch 3',
            'created_by' => $users['operator_timah']->id,
            'current_owner_partner_id' => $partners['antam']->id,
            'is_ready' => false,
        ]);

        // ... (Log aktivitas Batch 3 tetap sama)

        // ======================================
        // BATCH 4: Flow Processing -> Stocking
        // ======================================
        $batch4 = Batch::create([
            'batch_code' => 'TIM-CER-20251120-004',
            'lot_number' => 'L-CER-20251120-004-D',
            
            // PERBAIKAN DISINI: Gunakan ID 4 untuk MID-CE-OXI99
            'product_code_id' => 4, // MID-CE-OXI99
            
            'status' => 'ready',
            'initial_weight' => 30000,
            'current_weight' => 3600,
            'weight_unit' => 'kg',
            'tonase' => 30.00,
            'konsentrat_persen' => 12.00,
            'massa_ltj_kg' => 3600,
            'nd_content' => 10.00, 'y_content' => 15.00, 'ce_content' => 60.00, 'la_content' => 10.00, 'pr_content' => 5.00,
            'origin_location' => 'PT Pupuk Kujang',
            'current_location' => 'Warehouse PT Pupuk Kujang',
            'container_code' => 'K-TMH-004',
            'keterangan' => 'Stocking Cerium Oksida',
            'created_by' => $users['user_pupuk']->id,
            'current_owner_partner_id' => $partners['pupuk_kujang']->id,
            'is_ready' => true,
        ]);

        // ... (Log aktivitas Batch 4 tetap sama)

        // ======================================
        // BATCH 5: Flow Export -> Produksi Baterai
        // ======================================
        $batch5 = Batch::create([
            'batch_code' => 'TIM-MON-20251120-005',
            'lot_number' => 'L-MON-20251120-005-E',
            
            // PERBAIKAN DISINI
            'product_code_id' => 1, // TIM-MON-RAW
            
            'status' => 'delivered',
            'initial_weight' => 25000,
            'current_weight' => 3500,
            'weight_unit' => 'kg',
            'tonase' => 25.00,
            'konsentrat_persen' => 14.00,
            'massa_ltj_kg' => 3500,
            'nd_content' => 45.00, 'y_content' => 20.00, 'ce_content' => 15.00, 'la_content' => 12.00, 'pr_content' => 8.00,
            'origin_location' => 'PT Siemens Indonesia',
            'current_location' => 'PT Siemens Indonesia - Jakarta',
            'container_code' => 'K-TMH-005',
            'keterangan' => 'Produksi Baterai',
            'created_by' => $users['user_siemens']->id,
            'current_owner_partner_id' => $partners['siemens']->id,
            'is_ready' => true,
        ]);

        // ... (Log aktivitas Batch 5 tetap sama)

        // ======================================
        // BATCH 6: Flow Processing Magnet
        // ======================================
        $batch6 = Batch::create([
            'batch_code' => 'TIM-NEO-20251120-006',
            'lot_number' => 'L-NEO-20251120-006-F',
            
            // PERBAIKAN DISINI: Gunakan ID 3 untuk MID-ND-OXI99
            'product_code_id' => 3, // MID-ND-OXI99
            
            'status' => 'delivered',
            'initial_weight' => 15000,
            'current_weight' => 3000,
            'weight_unit' => 'kg',
            'tonase' => 15.00,
            'konsentrat_persen' => 20.00,
            'massa_ltj_kg' => 3000,
            'nd_content' => 75.00, 'y_content' => 10.00, 'ce_content' => 8.00, 'la_content' => 5.00, 'pr_content' => 2.00,
            'origin_location' => 'PT Astra Otoparts Tbk',
            'current_location' => 'Denso Indonesia - Jakarta',
            'container_code' => 'K-TMH-006',
            'keterangan' => 'Produksi Magnet Permanen',
            'created_by' => $users['user_astra']->id,
            'current_owner_partner_id' => $partners['denso']->id,
            'is_ready' => true,
        ]);

        // ... (Log aktivitas Batch 6 tetap sama)

        // ======================================
        // BATCH 7: Flow Processing -> Ekspor Produk
        // ======================================
        $batch7 = Batch::create([
            'batch_code' => 'TIM-MON-20251120-007',
            'lot_number' => 'L-MON-20251120-007-G',
            
            // PERBAIKAN DISINI
            'product_code_id' => 1, // TIM-MON-RAW
            
            'status' => 'delivered',
            'initial_weight' => 30000,
            'current_weight' => 2400,
            'weight_unit' => 'kg',
            'tonase' => 30.00,
            'konsentrat_persen' => 10.00,
            'massa_ltj_kg' => 3000,
            'nd_content' => 40.00, 'y_content' => 25.00, 'ce_content' => 18.00, 'la_content' => 12.00, 'pr_content' => 5.00,
            'origin_location' => 'PT Rekacipta Inovasi',
            'current_location' => 'Export - Stok produk jadi',
            'container_code' => 'K-TMH-007',
            'keterangan' => 'Stok produk jadi',
            'created_by' => $users['user_rekacipta']->id,
            'current_owner_partner_id' => $partners['ngk']->id,
            'is_ready' => true,
        ]);

        $allActivities[] = ['batch' => $batch7, 'timestamp' => $baseDate->copy()->addDays(1)->addHours(5), 'action' => 'RECEIVING_MATERIAL', 'user' => $users['user_rekacipta'], 'notes' => 'Diterima dari PT Mitra Stania. Input 1500 kg LTJ'];
        $allActivities[] = ['batch' => $batch7, 'timestamp' => $baseDate->copy()->addDays(2)->addHours(1), 'action' => 'PRODUKSI_LTJ', 'user' => $users['user_rekacipta'], 'notes' => 'Input proses: 3000 kg LTJ (Batch 7)'];
        $allActivities[] = ['batch' => $batch7, 'timestamp' => $baseDate->copy()->addDays(3)->addHours(5), 'action' => 'STOCKING_PRODUK', 'user' => $users['user_rekacipta'], 'notes' => 'Output: 1400 kg. Stok produk jadi'];
        $allActivities[] = ['batch' => $batch7, 'timestamp' => $baseDate->copy()->addDays(4)->addHours(5), 'action' => 'RECEIVING_END_USER', 'user' => $users['user_ngk'], 'notes' => 'Stok 1.4T produk jadi. Batch selesai'];

        // Sort by timestamp
        usort($allActivities, function($a, $b) {
            return $a['timestamp']->timestamp - $b['timestamp']->timestamp;
        });

        // Create logs
        $logCount = 0;
        foreach ($allActivities as $activity) {
            BatchLog::create([
                'batch_id' => $activity['batch']->id,
                'action' => $activity['action'],
                'actor_user_id' => $activity['user']->id,
                'notes' => $activity['notes'],
                'created_at' => $activity['timestamp'],
                'updated_at' => $activity['timestamp'],
            ]);
            $logCount++;
        }

        $this->command->info('   ✓ Batches: 7 batches');
        $this->command->info('   ✓ Activity logs: ' . $logCount . ' activities');
    }

    /**
     * Helper: Create batch
     */
    private function createBatch($data)
    {
        return Batch::create($data);
    }

    /**
     * Print summary
     */
    private function printSummary()
    {
        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('📋 LOGIN CREDENTIALS (Password: password)');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('🏢 PT TIMAH (Internal):');
        $this->command->info('  Super Admin  : superadmin@timah.com');
        $this->command->info('  Admin        : admin@timah.com');
        $this->command->info('  Operator     : operator@timah.com');
        $this->command->info('  Auditor      : auditor@timah.com');
        $this->command->info('');
        $this->command->info('👥 END USER:');
        $this->command->info('  PT Rekacipta : user@rekacipta.com');
        $this->command->info('  PT Len       : user@len.co.id');
        $this->command->info('  PT Pupuk     : user@pupukkujang.com');
        $this->command->info('  PT Pindad    : user@pindad.com');
        $this->command->info('  PT Dirgantara: user@indonesian-aerospace.com');
        $this->command->info('  Denso        : user@denso.co.id');
        $this->command->info('  Astra        : user@astraotoparts.com');
        $this->command->info('  NGK Busi     : user@ngkbusi.co.id');
        $this->command->info('  Siemens      : user@siemens.com');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('📊 DATABASE SUMMARY:');
        $this->command->info('  ✓ 7 Product Codes');
        $this->command->info('  ✓ 14 Partners (4 upstream + 10 end users)');
        $this->command->info('  ✓ 13 Users (multi-role)');
        $this->command->info('  ✓ 7 Batches dengan ~40 activity logs');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('🚀 Ready! Login at: http://localhost:8000');
        $this->command->info('');
    }
}