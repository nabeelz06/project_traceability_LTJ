<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductCodeSeeder extends Seeder
{
    public function run(): void
    {
        $productCodes = [
            // Original Product Codes
            [
                'code' => 'TIM-MON-RAW',
                'stage' => 'Upstream',
                'description' => 'Monasit Raw Material dari PT Timah',
                'material' => 'MON',
                'spec' => 'RAW',
                'category' => 'Raw Material',
                'specifications' => 'Monasit mentah dari tambang PT Timah',
            ],
            [
                'code' => 'MID-MON-CON',
                'stage' => 'Midstream',
                'description' => 'Monasit Concentrate',
                'material' => 'MON',
                'spec' => 'CON',
                'category' => 'Concentrated',
                'specifications' => 'Monasit terkonsentrasi untuk processing lanjutan',
            ],
            
            // Mineral Ikutan Product Codes
            [
                'code' => 'MIN-IKUTAN',
                'stage' => 'Upstream',
                'description' => 'Mineral Ikutan dari Wet Process',
                'material' => 'IKUTAN',
                'spec' => 'RAW',
                'category' => 'Raw Material',
                'specifications' => 'Campuran Zircon, Ilmenite, dan Monasit dari wet process',
            ],
            [
                'code' => 'DRY-ZIRCON-CON',
                'stage' => 'Midstream',
                'description' => 'Zircon Concentrate dari Dry Process',
                'material' => 'ZIRCON',
                'spec' => 'ZrO2>65%',
                'category' => 'Konsentrat',
                'specifications' => 'Zircon concentrate dengan kandungan ZrO2 > 65%',
            ],
            [
                'code' => 'DRY-ILMENITE-CON',
                'stage' => 'Midstream',
                'description' => 'Ilmenite Concentrate dari Dry Process',
                'material' => 'ILMENITE',
                'spec' => 'TiO2>50%',
                'category' => 'Konsentrat',
                'specifications' => 'Ilmenite concentrate dengan kandungan TiO2 > 50%',
            ],
            [
                'code' => 'DRY-MON-CON',
                'stage' => 'Midstream',
                'description' => 'Monasit Concentrate dari Dry Process',
                'material' => 'MON',
                'spec' => 'REO>55%',
                'category' => 'Konsentrat',
                'specifications' => 'Monasit concentrate dengan kandungan REO > 55%',
            ],
            [
                'code' => 'MON-LAB-SAMPLE',
                'stage' => 'Midstream',
                'description' => 'Monasit Sample untuk Analisis Lab',
                'material' => 'MON',
                'spec' => 'SAMPLE',
                'category' => 'Lab Sample',
                'specifications' => 'Sample Monasit untuk analisis kandungan LTJ (Nd, La, Ce, Y, Pr)',
            ],
        ];

        foreach ($productCodes as $code) {
            DB::table('product_codes')->updateOrInsert(
                ['code' => $code['code']],
                [
                    'stage' => $code['stage'],
                    'description' => $code['description'],
                    'material' => $code['material'],
                    'spec' => $code['spec'],
                    'category' => $code['category'],
                    'specifications' => $code['specifications'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

        $this->command->info('✅ Product codes seeded successfully!');
    }
}