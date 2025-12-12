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

        // 1. Seed Product Codes (7 original codes)
        $this->seedProductCodes();

        // 2. Seed Product Codes Baru (4 codes untuk mineral ikutan workflow)
        $this->call([
            NewProductCodesSeeder::class, 
        ]);

        // 3. Seed Partners
        $partners = $this->seedPartners();

        // 4. Seed Users (termasuk 4 operator baru + 2 regulator)
        $users = $this->seedUsers($partners);

        // 5. Seed 7 Batches dengan ~50 activity logs
        $this->seed7BatchesWithDetailedFlow($users, $partners);

        $this->command->info('');
        $this->command->info('✅ Database seeded successfully!');
        $this->printSummary();
    }

    /**
     * Seed 7 Product Codes Original
     */
    private function seedProductCodes()
    {
        $this->command->info('📦 Seeding original product codes...');

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

            // MIDSTREAM - Purified Oxides (5 LTJ elements)
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

        $this->command->info('   ✓ Original product codes: 7 kode produk');
    }

    /**
     * Seed Partners
     */
    private function seedPartners()
    {
        $this->command->info('🏢 Seeding partners...');
        
        $partners = [];

        // UPSTREAM Partners
        $partners['timah'] = Partner::updateOrCreate(
            ['name' => 'PT Timah Tbk'],
            [
                'type' => 'upstream',
                'pic_name' => 'Direktur Operasional PT Timah',
                'pic_phone' => '021-5063800',
                'address' => 'Jl. Jend. Sudirman Kav. 36, Jakarta Selatan',
                'allowed_product_codes' => json_encode(['TIM-MON-RAW', 'MIN-IKUTAN']),
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

        // END USER Partners
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
     * Seed Users (Updated - termasuk 4 operator + 2 regulator)
     */
    private function seedUsers($partners)
    {
        $this->command->info('👥 Seeding users...');
        
        $users = [];

        // ===== ADMIN & SUPER ADMIN =====
        $users['superadmin'] = User::updateOrCreate(
            ['email' => 'superadmin@pttimah.com'],
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'partner_id' => null,
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        $users['admin'] = User::updateOrCreate(
            ['email' => 'admin@pttimah.com'],
            [
                'name' => 'Admin Operasional PT Timah',
                'username' => 'admin.timah',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'partner_id' => null,
                'phone' => '081234567891',
                'is_active' => true,
            ]
        );

        // ===== 4 OPERATOR BARU (MINERAL IKUTAN WORKFLOW) =====
        $users['wet_operator'] = User::updateOrCreate(
            ['email' => 'wet@pttimah.com'],
            [
                'name' => 'Operator Wet Process',
                'username' => 'wet.operator',
                'password' => Hash::make('password'),
                'role' => 'wet_operator',
                'partner_id' => $partners['timah']->id,
                'phone' => '081234567801',
                'is_active' => true,
            ]
        );

        $users['dry_operator'] = User::updateOrCreate(
            ['email' => 'dry@pttimah.com'],
            [
                'name' => 'Operator Dry Process',
                'username' => 'dry.operator',
                'password' => Hash::make('password'),
                'role' => 'dry_operator',
                'partner_id' => $partners['timah']->id,
                'phone' => '081234567802',
                'is_active' => true,
            ]
        );

        $users['warehouse_operator'] = User::updateOrCreate(
            ['email' => 'warehouse@pttimah.com'],
            [
                'name' => 'Operator Warehouse',
                'username' => 'warehouse.operator',
                'password' => Hash::make('password'),
                'role' => 'warehouse_operator',
                'partner_id' => $partners['timah']->id,
                'phone' => '081234567803',
                'is_active' => true,
            ]
        );

        $users['lab_operator'] = User::updateOrCreate(
            ['email' => 'lab@pttimah.com'],
            [
                'name' => 'Operator Lab/Project Plan',
                'username' => 'lab.operator',
                'password' => Hash::make('password'),
                'role' => 'lab_operator',
                'partner_id' => $partners['timah']->id,
                'phone' => '081234567804',
                'is_active' => true,
            ]
        );

        // ===== REGULATOR (BIM & ESDM) =====
        $users['regulator_bim'] = User::updateOrCreate(
            ['email' => 'bim@esdm.go.id'],
            [
                'name' => 'Regulator BIM',
                'username' => 'regulator.bim',
                'password' => Hash::make('password'),
                'role' => 'g_bim',
                'partner_id' => null, // Government tidak terikat partner
                'phone' => '081234567810',
                'is_active' => true,
            ]
        );

        $users['regulator_esdm'] = User::updateOrCreate(
            ['email' => 'esdm@esdm.go.id'],
            [
                'name' => 'Regulator ESDM',
                'username' => 'regulator.esdm',
                'password' => Hash::make('password'),
                'role' => 'g_esdm',
                'partner_id' => null, // Government tidak terikat partner
                'phone' => '081234567811',
                'is_active' => true,
            ]
        );

        // ===== ORIGINAL OPERATOR (RFID SCANNING) =====
        $users['operator_timah'] = User::updateOrCreate(
            ['email' => 'operator@pttimah.com'],
            [
                'name' => 'Operator Lapangan PT Timah',
                'username' => 'operator.timah',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'partner_id' => $partners['timah']->id,
                'phone' => '081234567892',
                'is_active' => true,
            ]
        );

        // ===== END USER PARTNERS =====
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
            ['email' => 'auditor@pttimah.com'],
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

        $this->command->info('   ✓ Users: ' . count($users) . ' accounts');

        return $users;
    }

    /**
     * Seed 7 Batches dengan detailed flow
     */
    private function seed7BatchesWithDetailedFlow($users, $partners)
    {
        $this->command->info('📦 Seeding 7 batches with detailed flow...');

        $baseDate = Carbon::create(2025, 11, 20, 8, 0, 0);
        $allActivities = [];

        // ===== BATCH 1: Flow Lengkap =====
        $batch1 = Batch::create([
            'batch_code' => 'TIM-MON-20251120-001',
            'lot_number' => 'L-MON-20251120-001-A',
            'product_code_id' => 1, // TIM-MON-RAW
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

        $allActivities[] = ['batch' => $batch1, 'timestamp' => $baseDate->copy(), 'action' => 'BATCH_CREATED', 'user' => $users['operator_timah'], 'notes' => 'Batch dibuat - 20T monasit dari PT Timah'];
        $allActivities[] = ['batch' => $batch1, 'timestamp' => $baseDate->copy()->addHours(2), 'action' => 'RECEIVING_MATERIAL', 'user' => $users['user_rekacipta'], 'notes' => 'Diterima dari PT Timah. Input 3000 kg LTJ'];
        $allActivities[] = ['batch' => $batch1, 'timestamp' => $baseDate->copy()->addDays(1)->addHours(5), 'action' => 'PRODUKSI_LTJ', 'user' => $users['user_rekacipta'], 'notes' => 'Input proses: 3000 kg LTJ (Batch 1)'];
        $allActivities[] = ['batch' => $batch1, 'timestamp' => $baseDate->copy()->addDays(2)->addHours(3), 'action' => 'OUTPUT_PRODUKSI', 'user' => $users['user_rekacipta'], 'notes' => 'Output: 1200 kg Neodymium Oksida 99%'];
        $allActivities[] = ['batch' => $batch1, 'timestamp' => $baseDate->copy()->addDays(3), 'action' => 'SHIPPING_PRODUCT', 'user' => $users['user_rekacipta'], 'notes' => 'Kirim 1.2T ke PT Dirgantara Indonesia'];
        $allActivities[] = ['batch' => $batch1, 'timestamp' => $baseDate->copy()->addDays(4), 'action' => 'RECEIVING_END_USER', 'user' => $users['user_dirgantara'], 'notes' => 'Diterima 1.2T Neodymium untuk produksi komponen pesawat'];

        // ===== BATCH 2: Flow dengan Export Track =====
        $batch2 = Batch::create([
            'batch_code' => 'TIM-MON-20251120-002',
            'lot_number' => 'L-MON-20251120-002-B',
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

        $allActivities[] = ['batch' => $batch2, 'timestamp' => $baseDate->copy()->addHours(1), 'action' => 'BATCH_CREATED', 'user' => $users['operator_timah'], 'notes' => 'Batch dibuat - 30T monasit mentah'];
        $allActivities[] = ['batch' => $batch2, 'timestamp' => $baseDate->copy()->addDays(1), 'action' => 'RECEIVING_MATERIAL', 'user' => $users['user_len'], 'notes' => 'Diterima dari PT Timah. Input 3000 kg LTJ'];
        $allActivities[] = ['batch' => $batch2, 'timestamp' => $baseDate->copy()->addDays(2)->addHours(2), 'action' => 'PRODUKSI_LTJ', 'user' => $users['user_len'], 'notes' => 'Proses: 3000 kg LTJ (Batch 2)'];
        $allActivities[] = ['batch' => $batch2, 'timestamp' => $baseDate->copy()->addDays(3)->addHours(4), 'action' => 'OUTPUT_PRODUKSI', 'user' => $users['user_len'], 'notes' => 'Output: 1500 kg Yttrium Oksida'];
        $allActivities[] = ['batch' => $batch2, 'timestamp' => $baseDate->copy()->addDays(4)->addHours(1), 'action' => 'SHIPPING_PRODUCT', 'user' => $users['user_len'], 'notes' => 'Kirim 1.5T ke NGK Busi Indonesia'];
        $allActivities[] = ['batch' => $batch2, 'timestamp' => $baseDate->copy()->addDays(5), 'action' => 'RECEIVING_END_USER', 'user' => $users['user_ngk'], 'notes' => 'Diterima 1.5T Yttrium untuk produksi busi'];

        // ===== BATCH 3: Flow Kompleks =====
        $batch3 = Batch::create([
            'batch_code' => 'TIM-MON-20251120-003',
            'lot_number' => 'L-MON-20251120-003-C',
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

        $allActivities[] = ['batch' => $batch3, 'timestamp' => $baseDate->copy()->addHours(3), 'action' => 'BATCH_CREATED', 'user' => $users['operator_timah'], 'notes' => 'Batch dibuat - 50T monasit untuk processing'];
        $allActivities[] = ['batch' => $batch3, 'timestamp' => $baseDate->copy()->addDays(1)->addHours(1), 'action' => 'IN_PROCESSING', 'user' => $users['operator_timah'], 'notes' => 'Masuk proses pemisahan LTJ (Batch 3)'];

        // ===== BATCH 4: Flow Processing -> Stocking =====
        $batch4 = Batch::create([
            'batch_code' => 'TIM-CER-20251120-004',
            'lot_number' => 'L-CER-20251120-004-D',
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

        $allActivities[] = ['batch' => $batch4, 'timestamp' => $baseDate->copy()->addHours(4), 'action' => 'BATCH_CREATED', 'user' => $users['operator_timah'], 'notes' => 'Batch dibuat - 30T untuk Pupuk Kujang'];
        $allActivities[] = ['batch' => $batch4, 'timestamp' => $baseDate->copy()->addDays(1)->addHours(3), 'action' => 'RECEIVING_MATERIAL', 'user' => $users['user_pupuk'], 'notes' => 'Diterima 30T monasit. Input 3600 kg LTJ'];
        $allActivities[] = ['batch' => $batch4, 'timestamp' => $baseDate->copy()->addDays(2)->addHours(2), 'action' => 'PRODUKSI_LTJ', 'user' => $users['user_pupuk'], 'notes' => 'Proses: 3600 kg LTJ -> Cerium Oksida'];
        $allActivities[] = ['batch' => $batch4, 'timestamp' => $baseDate->copy()->addDays(3)->addHours(1), 'action' => 'STOCKING_PRODUK', 'user' => $users['user_pupuk'], 'notes' => 'Output: 3.6T Cerium Oksida ready'];

        // ===== BATCH 5: Flow Export -> Produksi Baterai =====
        $batch5 = Batch::create([
            'batch_code' => 'TIM-MON-20251120-005',
            'lot_number' => 'L-MON-20251120-005-E',
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

        $allActivities[] = ['batch' => $batch5, 'timestamp' => $baseDate->copy()->addHours(5), 'action' => 'BATCH_CREATED', 'user' => $users['operator_timah'], 'notes' => 'Batch dibuat - 25T untuk Siemens'];
        $allActivities[] = ['batch' => $batch5, 'timestamp' => $baseDate->copy()->addDays(1)->addHours(4), 'action' => 'RECEIVING_MATERIAL', 'user' => $users['user_siemens'], 'notes' => 'Diterima 25T monasit. Input 3500 kg LTJ'];
        $allActivities[] = ['batch' => $batch5, 'timestamp' => $baseDate->copy()->addDays(2)->addHours(1), 'action' => 'PRODUKSI_LTJ', 'user' => $users['user_siemens'], 'notes' => 'Proses: 3500 kg LTJ untuk baterai'];
        $allActivities[] = ['batch' => $batch5, 'timestamp' => $baseDate->copy()->addDays(3)->addHours(3), 'action' => 'OUTPUT_PRODUKSI', 'user' => $users['user_siemens'], 'notes' => 'Output: 3.5T material baterai'];

        // ===== BATCH 6: Flow Processing Magnet =====
        $batch6 = Batch::create([
            'batch_code' => 'TIM-NEO-20251120-006',
            'lot_number' => 'L-NEO-20251120-006-F',
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

        $allActivities[] = ['batch' => $batch6, 'timestamp' => $baseDate->copy()->addHours(6), 'action' => 'BATCH_CREATED', 'user' => $users['operator_timah'], 'notes' => 'Batch dibuat - 15T untuk Astra'];
        $allActivities[] = ['batch' => $batch6, 'timestamp' => $baseDate->copy()->addDays(1)->addHours(2), 'action' => 'RECEIVING_MATERIAL', 'user' => $users['user_astra'], 'notes' => 'Diterima 15T monasit. Input 3000 kg LTJ'];
        $allActivities[] = ['batch' => $batch6, 'timestamp' => $baseDate->copy()->addDays(2)->addHours(4), 'action' => 'PRODUKSI_LTJ', 'user' => $users['user_astra'], 'notes' => 'Proses: 3000 kg LTJ -> Neodymium Oksida'];
        $allActivities[] = ['batch' => $batch6, 'timestamp' => $baseDate->copy()->addDays(3)->addHours(2), 'action' => 'OUTPUT_PRODUKSI', 'user' => $users['user_astra'], 'notes' => 'Output: 3T Neodymium untuk magnet'];
        $allActivities[] = ['batch' => $batch6, 'timestamp' => $baseDate->copy()->addDays(4)->addHours(2), 'action' => 'SHIPPING_PRODUCT', 'user' => $users['user_astra'], 'notes' => 'Kirim 3T ke Denso Indonesia'];
        $allActivities[] = ['batch' => $batch6, 'timestamp' => $baseDate->copy()->addDays(5)->addHours(1), 'action' => 'RECEIVING_END_USER', 'user' => $users['user_denso'], 'notes' => 'Diterima 3T untuk produksi komponen otomotif'];

        // ===== BATCH 7: Flow Processing -> Ekspor Produk =====
        $batch7 = Batch::create([
            'batch_code' => 'TIM-MON-20251120-007',
            'lot_number' => 'L-MON-20251120-007-G',
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

        // Sort activities by timestamp
        usort($allActivities, function($a, $b) {
            return $a['timestamp']->timestamp - $b['timestamp']->timestamp;
        });

        // Create batch logs
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
        $this->command->info('  Super Admin       : superadmin@pttimah.com');
        $this->command->info('  Admin             : admin@pttimah.com');
        $this->command->info('  Operator          : operator@pttimah.com');
        $this->command->info('  Auditor           : auditor@pttimah.com');
        $this->command->info('');
        $this->command->info('🔧 MINERAL IKUTAN OPERATORS:');
        $this->command->info('  Wet Process       : wet@pttimah.com');
        $this->command->info('  Dry Process       : dry@pttimah.com');
        $this->command->info('  Warehouse         : warehouse@pttimah.com');
        $this->command->info('  Lab/Project Plan  : lab@pttimah.com');
        $this->command->info('');
        $this->command->info('🏛️ REGULATOR:');
        $this->command->info('  BIM               : bim@esdm.go.id');
        $this->command->info('  ESDM              : esdm@esdm.go.id');
        $this->command->info('');
        $this->command->info('👥 END USER:');
        $this->command->info('  PT Rekacipta      : user@rekacipta.com');
        $this->command->info('  PT Len            : user@len.co.id');
        $this->command->info('  PT Pupuk Kujang   : user@pupukkujang.com');
        $this->command->info('  PT Pindad         : user@pindad.com');
        $this->command->info('  PT Dirgantara     : user@indonesian-aerospace.com');
        $this->command->info('  Denso             : user@denso.co.id');
        $this->command->info('  Astra Otoparts    : user@astraotoparts.com');
        $this->command->info('  NGK Busi          : user@ngkbusi.co.id');
        $this->command->info('  Siemens           : user@siemens.com');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('📊 DATABASE SUMMARY:');
        $this->command->info('  ✓ 11 Product Codes (7 original + 4 new)');
        $this->command->info('  ✓ 14 Partners (4 upstream + 10 end users)');
        $this->command->info('  ✓ 19 Users (4 operators + 2 regulators + 13 others)');
        $this->command->info('  ✓ 7 Batches dengan activity logs');
        $this->command->info('========================================');
        $this->command->info('');
        $this->command->info('🚀 Ready! Login at: http://localhost:8000');
        $this->command->info('');
    }
}