<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incident_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mitre_id');
            $table->timestamp('last_occurrence');
            $table->string('severity');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_groups');
    }
};
