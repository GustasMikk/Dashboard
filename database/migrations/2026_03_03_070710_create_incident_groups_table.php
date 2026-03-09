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
            $table->string('title');
            $table->string('mitre_id');
            $table->integer('total_occurrences');
            $table->timestamp('last_occurrence_at');
            $table->string('highest_severity');
            $table->enum('status', ['open', 'assigned', 'resolved', 'closed'])
                ->default('open')
                ->index();
            $table->foreignId('assigned_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('host');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('ai_description')->nullable();
            $table->text('ai_recommendations')->nullable();
            $table->text('ai_root_cause')->nullable();
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
