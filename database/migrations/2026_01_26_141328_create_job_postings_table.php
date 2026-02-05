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
        Schema::create('job_postings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('job_title');
            $table->string('job_type');
            $table->integer('job_minimum_compensation');
            $table->integer('job_maximum_compensation');
            $table->string('job_compensation_currency');
            $table->string('job_compensation_type');
            $table->string('job_address_country');
            $table->string('job_address_administrative_area')->nullable();
            $table->string('job_address_municipality');
            $table->string('job_address_street')->nullable();
            $table->string('job_address_postal_code')->nullable();
            $table->json('job_stack');
            $table->json('job_description')->nullable();
            $table->string('status');
            $table->string('link')->nullable();
            $table->foreignUuid('employer_id')->constrained('users', 'id');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
