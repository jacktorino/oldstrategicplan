<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            $table->foreignId('strategic_plan_id')
                ->nullable()
                ->after('id')
                ->constrained('strategic_plans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kpis', function (Blueprint $table) {
            $table->dropForeign(['strategic_plan_id']);
            $table->dropColumn('strategic_plan_id');
        });
    }
};
