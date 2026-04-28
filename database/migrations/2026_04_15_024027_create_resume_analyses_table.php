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
        Schema::create('resume_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');

            $table->string('candidate_name')->nullable();
            $table->longText('summary')->nullable();

            $table->json('core_skills')->nullable();
            $table->json('missing_skills')->nullable();
            $table->json('interview_questions')->nullable();

            $table->float('confidence_score')->default(0);
            $table->string('grade')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('recommendation')->nullable();
            $table->string('most_recent_role')->nullable();
            $table->boolean('shortlist')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_analyses');
    }
};
