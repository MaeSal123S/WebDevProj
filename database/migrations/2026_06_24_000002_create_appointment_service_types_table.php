<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create the pivot table
        Schema::create('appointment_service_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('appointment_id');
            $table->unsignedInteger('service_type_id');
            $table->unique(['appointment_id', 'service_type_id']);
        });

        // Migrate existing single service_type_id values into the pivot table
        $appointments = DB::table('appointments')
            ->whereNotNull('service_type_id')
            ->get(['appointment_id', 'service_type_id']);

        foreach ($appointments as $apt) {
            DB::table('appointment_service_types')->insertOrIgnore([
                'appointment_id'  => $apt->appointment_id,
                'service_type_id' => $apt->service_type_id,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_service_types');
    }
};
