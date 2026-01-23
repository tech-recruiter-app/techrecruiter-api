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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('address_country')->after('password');
            $table->string('address_administrative_area')->after('address_country')->nullable();
            $table->string('address_municipality')->after('address_administrative_area');
            $table->string('address_street')->after('address_municipality')->nullable();
            $table->string('address_postal_code')->after('address_street')->nullable();
        });
    }
};
