<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration untuk menambahkan field LTJ ke tabel batches
     * COMPLETED: Mencakup field LTJ, GPS, Evidence, Process, dan RFID
     * FIXED: Menghapus ->comment() karena tidak disupport oleh Nile Postgres
     */
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            
            // 1. Data Tonase & Konsentrat
            if (!Schema::hasColumn('batches', 'tonase')) {
                $table->decimal('tonase', 10, 2)->nullable()->after('weight_unit');
            }
            if (!Schema::hasColumn('batches', 'konsentrat_persen')) {
                $after = Schema::hasColumn('batches', 'tonase') ? 'tonase' : 'weight_unit';
                $table->decimal('konsentrat_persen', 5, 2)->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'massa_ltj_kg')) {
                $after = Schema::hasColumn('batches', 'konsentrat_persen') ? 'konsentrat_persen' : 'weight_unit';
                $table->decimal('massa_ltj_kg', 10, 2)->nullable()->after($after);
            }
            
            // 2. 5 Unsur LTJ (Logam Tanah Jarang)
            if (!Schema::hasColumn('batches', 'nd_content')) {
                $after = Schema::hasColumn('batches', 'massa_ltj_kg') ? 'massa_ltj_kg' : 'weight_unit';
                $table->decimal('nd_content', 5, 2)->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'y_content')) {
                $after = Schema::hasColumn('batches', 'nd_content') ? 'nd_content' : 'weight_unit';
                $table->decimal('y_content', 5, 2)->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'ce_content')) {
                $after = Schema::hasColumn('batches', 'y_content') ? 'y_content' : 'weight_unit';
                $table->decimal('ce_content', 5, 2)->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'la_content')) {
                $after = Schema::hasColumn('batches', 'ce_content') ? 'ce_content' : 'weight_unit';
                $table->decimal('la_content', 5, 2)->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'pr_content')) {
                $after = Schema::hasColumn('batches', 'la_content') ? 'la_content' : 'weight_unit';
                $table->decimal('pr_content', 5, 2)->nullable()->after($after);
            }
            
            // 3. GPS & Location Tracking
            if (!Schema::hasColumn('batches', 'origin_location')) {
                $table->string('origin_location')->nullable()->after('current_location');
            }
            if (!Schema::hasColumn('batches', 'current_latitude')) {
                $after = Schema::hasColumn('batches', 'origin_location') ? 'origin_location' : 'current_location';
                $table->decimal('current_latitude', 10, 8)->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'current_longitude')) {
                $after = Schema::hasColumn('batches', 'current_latitude') ? 'current_latitude' : 'current_location';
                $table->decimal('current_longitude', 11, 8)->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'current_location_name')) {
                $after = Schema::hasColumn('batches', 'current_longitude') ? 'current_longitude' : 'current_location';
                $table->string('current_location_name')->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'last_gps_update')) {
                $after = Schema::hasColumn('batches', 'current_location_name') ? 'current_location_name' : 'current_location';
                $table->timestamp('last_gps_update')->nullable()->after($after);
            }
            
            // 4. Evidence (Bukti Foto/Video/Dokumen)
            if (!Schema::hasColumn('batches', 'evidence_photos')) {
                $after = Schema::hasColumn('batches', 'last_gps_update') ? 'last_gps_update' : 'current_location';
                $table->json('evidence_photos')->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'evidence_videos')) {
                $after = Schema::hasColumn('batches', 'evidence_photos') ? 'evidence_photos' : 'current_location';
                $table->json('evidence_videos')->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'evidence_documents')) {
                $after = Schema::hasColumn('batches', 'evidence_videos') ? 'evidence_videos' : 'current_location';
                $table->json('evidence_documents')->nullable()->after($after);
            }
            
            // 5. Process Parameters (Energi, Air, Suhu, dll)
            if (!Schema::hasColumn('batches', 'energy_input_kwh')) {
                $after = Schema::hasColumn('batches', 'evidence_documents') ? 'evidence_documents' : 'current_location';
                $table->decimal('energy_input_kwh', 10, 2)->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'water_consumption_liter')) {
                $after = Schema::hasColumn('batches', 'energy_input_kwh') ? 'energy_input_kwh' : 'current_location';
                $table->decimal('water_consumption_liter', 10, 2)->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'process_temperature_celsius')) {
                $after = Schema::hasColumn('batches', 'water_consumption_liter') ? 'water_consumption_liter' : 'current_location';
                $table->decimal('process_temperature_celsius', 6, 2)->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'process_ph')) {
                $after = Schema::hasColumn('batches', 'process_temperature_celsius') ? 'process_temperature_celsius' : 'current_location';
                $table->decimal('process_ph', 4, 2)->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'reaction_time_minutes')) {
                $after = Schema::hasColumn('batches', 'process_ph') ? 'process_ph' : 'current_location';
                $table->integer('reaction_time_minutes')->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'efficiency_percent')) {
                $after = Schema::hasColumn('batches', 'reaction_time_minutes') ? 'reaction_time_minutes' : 'current_location';
                $table->decimal('efficiency_percent', 5, 2)->nullable()->after($after);
            }
            
            // 6. Additional Info (Lot Number)
            if (!Schema::hasColumn('batches', 'lot_number')) {
                $table->string('lot_number')->nullable()->after('batch_code');
            }
            
            // 7. RFID Information (UID & Full Tag)
            if (!Schema::hasColumn('batches', 'rfid_tag_uid')) {
                $after = Schema::hasColumn('batches', 'lot_number') ? 'lot_number' : 'batch_code';
                $table->string('rfid_tag_uid')->nullable()->after($after);
            }
            if (!Schema::hasColumn('batches', 'rfid_tag_full')) {
                $after = Schema::hasColumn('batches', 'rfid_tag_uid') ? 'rfid_tag_uid' : 'lot_number';
                $table->string('rfid_tag_full')->nullable()->after($after);
            }

            // 8. Keterangan Tambahan
            if (!Schema::hasColumn('batches', 'keterangan')) {
                $after = Schema::hasColumn('batches', 'history_log') ? 'history_log' : 'updated_at';
                $table->text('keterangan')->nullable()->after($after);
            }
        });
    }

    /**
     * Rollback migration
     */
    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $columns = [
                'tonase', 'konsentrat_persen', 'massa_ltj_kg',
                'nd_content', 'y_content', 'ce_content', 'la_content', 'pr_content',
                'origin_location', 'current_latitude', 'current_longitude', 'current_location_name', 'last_gps_update',
                'evidence_photos', 'evidence_videos', 'evidence_documents',
                'energy_input_kwh', 'water_consumption_liter', 'process_temperature_celsius', 'process_ph', 'reaction_time_minutes', 'efficiency_percent',
                'lot_number', 'rfid_tag_uid', 'rfid_tag_full', 'keterangan'
            ];
            
            $columnsToDrop = [];
            foreach ($columns as $col) {
                if (Schema::hasColumn('batches', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};