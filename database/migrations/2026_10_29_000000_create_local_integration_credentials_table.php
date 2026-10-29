<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('local_integration_credentials', function (Blueprint $table) {
            $table->string('provider')->primary();
            $table->text('encrypted_credentials');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_integration_credentials');
    }
};
