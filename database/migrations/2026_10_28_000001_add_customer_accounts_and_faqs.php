<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
        });

        Schema::create('faq_entries', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        $now = now();
        DB::table('faq_entries')->insert(collect([
            ['How often should my aircon be cleaned?', 'For typical residential use, cleaning every three to six months is generally recommended. Heavy daily use or reduced airflow may require more frequent service.'],
            ['How long does an appointment take?', 'Aircon cleaning usually takes around 75 minutes for the first unit. Additional units extend the appointment.'],
            ['What should I prepare before the team arrives?', 'Please clear the work area around the aircon, secure pets, and ensure electricity and water are available.'],
            ['Can I cancel my booking?', 'Use the private appointment link to cancel at least 24 hours before service. For later changes, contact our office.'],
            ['What arrival times can I choose?', 'Available booking windows are 9:00 AM, 1:00 PM, and 4:00 PM. The booking form shows whether each window is available, reserved, or in progress.'],
            ['How can I pay?', 'Pay in cash after the completed service. PayPal sandbox is a test-only presentation feature and does not collect real payments.'],
            ['Are maintenance plans available?', 'Not yet. Quarterly and biannual options are being evaluated. One-time cleaning appointments remain available.'],
            ['How do I provide my service location?', 'Enter your street address, Iligan City barangay, and a nearby landmark. No GPS sharing is required.'],
            ['Do you repair aircon units?', 'IcyBreeze focuses on cleaning. If we identify a likely repair issue, we will advise you to arrange appropriate repair service.'],
        ])->map(fn ($row, $index) => [
            'question' => $row[0], 'answer' => $row[1], 'sort_order' => $index + 1,
            'is_published' => true, 'created_at' => $now, 'updated_at' => $now,
        ])->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_entries');
        Schema::table('appointments', fn (Blueprint $table) => $table->dropConstrainedForeignId('user_id'));
    }
};
