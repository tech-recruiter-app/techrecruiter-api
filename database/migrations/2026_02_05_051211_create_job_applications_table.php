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
        Schema::create('job_applications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_posting_id')->constrained('job_postings');
            $table->foreignUuid('applicant_id')->constrained('users', 'id');
            $table->string('resume_link');
            $table->string('cover_letter_link')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
