<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\ParkingRecordActionEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\ParkingSpace::class)->constrained()->cascadeOnDelete();
            $table->enum('action', [
                ParkingRecordActionEnum::BLOCKED, ParkingRecordActionEnum::UNBLOCKED,
                ParkingRecordActionEnum::DRIVE_IN, ParkingRecordActionEnum::DRIVE_OUT
            ]);
            $table->string('registration_plates')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
