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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('wazuh_incident_id');
            $table->string('mitre_id')
                ->nullable();
            $table->string('title');
            $table->string('severity')->index();
            $table->string('rule');
            $table->string('host');
            $table->foreignId('incident_group_id')
                ->nullable()
                ->constrained('incident_groups')
                ->nullOnDelete();
            $table->json('raw_payload')->nullable();
            $table->timestamp('first_occurrence_at');
            $table->unsignedInteger('occurrences_count')->defaul(1);
            $table->timestamp('last_occurrence_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
