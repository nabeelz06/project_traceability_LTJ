<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCode;

class ProductCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productCodes = [
            // Mineral Ikutan - Raw Material (dari Wet Process)
            [
                'code' => 'WET-MIN-RAW',
                'stage' => 'upstream',
                'material' => 'MIN',
                'spec' => 'RAW',
                'description' => 'Mineral Ikutan Raw Material',
                'category' => 'raw_material',
            ],
            
            // Konsentrat dari Dry Process
            [
                'code' => 'DRY-ZIRCON-CON',
                'stage' => 'middlestream',
                'material' => 'ZIRCON',
                'spec' => 'CON',
                'description' => 'Zircon Concentrate from Dry Process',
                'category' => 'concentrate',
            ],
            [
                'code' => 'DRY-ILMENITE-CON',
                'stage' => 'middlestream',
                'material' => 'ILMENITE',
                'spec' => 'CON',
                'description' => 'Ilmenite Concentrate from Dry Process',
                'category' => 'concentrate',
            ],
            [
                'code' => 'DRY-MON-CON',
                'stage' => 'middlestream',
                'material' => 'MON',
                'spec' => 'CON',
                'description' => 'Monasit Concentrate from Dry Process',
                'category' => 'concentrate',
            ],
            
            // Lab Sample (untuk monasit yang di-split)
            [
                'code' => 'LAB-MON-SAMPLE',
                'stage' => 'middlestream',
                'material' => 'MON',
                'spec' => 'SAMPLE',
                'description' => 'Monasit Sample for Lab Analysis',
                'category' => 'sample',
            ],
        ];

        foreach ($productCodes as $code) {
            ProductCode::updateOrCreate(
                ['code' => $code['code']], // Find by code
                $code // Update or create with these values
            );
        }

        $this->command->info('✅ Product codes seeded successfully!');
        $this->command->info('📦 Total: ' . count($productCodes) . ' product codes');
    }
}