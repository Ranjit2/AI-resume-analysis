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
        Schema::table('resume_analyses', function (Blueprint $table) {
            $table->json('work_experience')->nullable()->after('most_recent_role');
        });
    }

    public function down(): void
    {
        Schema::table('resume_analyses', function (Blueprint $table) {
            $table->dropColumn('work_experience');
        });
    }
};
