<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('password');
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('short_description');
            $table->text('description');
            $table->unsignedInteger('price_centavos');
            $table->unsignedSmallInteger('duration_minutes');
            $table->unsignedSmallInteger('buffer_minutes')->default(30);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->index();
            $table->string('phone')->index();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('manage_token', 64)->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('unit_type');
            $table->unsignedTinyInteger('quantity')->default(1);
            $table->dateTime('starts_at')->index();
            $table->dateTime('ends_at')->index();
            $table->string('status')->index();
            $table->string('payment_status')->index();
            $table->string('source')->default('web');
            $table->unsignedInteger('subtotal_centavos');
            $table->unsignedInteger('travel_fee_centavos')->default(0);
            $table->unsignedInteger('total_centavos');
            $table->string('address_line');
            $table->string('barangay');
            $table->string('city');
            $table->string('province')->default('Lanao del Norte');
            $table->string('postal_code', 10)->nullable();
            $table->string('landmark')->nullable();
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();
            $table->index(['status', 'starts_at']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('method');
            $table->string('provider')->nullable();
            $table->string('reference')->nullable()->unique();
            $table->string('status')->index();
            $table->unsignedInteger('amount_centavos');
            $table->string('currency', 3)->default('PHP');
            $table->dateTime('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('appointment_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('actor_type');
            $table->string('actor_name');
            $table->string('reason')->nullable();
            $table->dateTime('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_status_histories');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('services');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }
};
