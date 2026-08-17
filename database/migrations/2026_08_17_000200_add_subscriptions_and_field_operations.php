<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('role');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('technician_id')->nullable()->after('service_id')->constrained('users')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable()->after('landmark');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->decimal('location_accuracy_meters', 8, 2)->nullable()->after('longitude');
            $table->dateTime('location_consent_at')->nullable()->after('location_accuracy_meters');
            $table->index(['technician_id', 'starts_at']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('manage_token', 64)->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('plan');
            $table->unsignedTinyInteger('interval_months');
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->unsignedInteger('price_per_visit_centavos');
            $table->string('status')->default('pending')->index();
            $table->date('next_service_date')->nullable()->index();
            $table->string('preferred_day')->nullable();
            $table->string('preferred_time')->nullable();
            $table->string('address_line');
            $table->string('barangay');
            $table->string('city')->default('Iligan City');
            $table->string('province')->default('Lanao del Norte');
            $table->string('postal_code', 10)->nullable();
            $table->string('landmark')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->dateTime('location_consent_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('technician_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->dateTime('recorded_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_locations');
        Schema::dropIfExists('subscriptions');

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['technician_id']);
            $table->dropIndex(['technician_id', 'starts_at']);
            $table->dropColumn(['technician_id', 'latitude', 'longitude', 'location_accuracy_meters', 'location_consent_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'is_active']);
        });
    }
};
