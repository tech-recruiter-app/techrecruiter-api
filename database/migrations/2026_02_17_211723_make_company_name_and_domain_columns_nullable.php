<?php

declare(strict_types=1);

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
        Schema::table('employer_profiles', function (Blueprint $table): void {
            $table->string('company_name')->unique()->change();
            $table->string('company_domain')->unique()->change();
        });
    }
};
