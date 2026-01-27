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
        Schema::create('employer_profiles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('company_name');
            $table->string('company_domain');
            $table->text('company_description');
            $table->timestamps();
        });
    }
};
