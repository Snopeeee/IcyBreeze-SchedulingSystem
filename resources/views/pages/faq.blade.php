@extends('layouts.site')
@php($title = 'Frequently Asked Questions')
@section('content')
<section class="page-hero"><div class="site-shell page-intro"><span class="eyebrow">Customer information</span><h1>Frequently Asked Questions</h1><p>Review information about scheduling, service preparation, payments, and future maintenance options.</p></div></section>
<section class="section"><div class="site-shell faq-list">
@foreach([
['How often should my aircon be cleaned?','For typical residential use, professional cleaning every three to six months is generally recommended. Heavy daily use, pets, construction dust, or reduced airflow may require more frequent service.'],
['How long does one appointment take?','Aircon cleaning usually takes around 75 minutes for the first unit. Additional units add time to the appointment.'],
['What should I prepare before the cleaning team arrives?','Please clear the work area below and around the aircon, secure pets, and ensure that electricity and water are available.'],
['Can I cancel or change my booking?','The private appointment-management link allows you to review the booking. Online cancellation is available until 24 hours before the appointment; for later changes, contact our office directly.'],
['What arrival times can I choose?','Live booking windows are available at 9:00 AM, 1:00 PM, and 4:00 PM. The schedule marks each window as available, reserved, or in progress before you continue.'],
['How can I pay?','Payment is collected in cash after the completed service. The office records the payment status in the system for reconciliation.'],
['Are recurring maintenance plans available?','Not yet. Quarterly and biannual maintenance options are being evaluated and will be announced when pricing and operating details are finalized. One-time cleaning appointments remain available.'],
['How do I provide my service location?','Enter your full street address, Iligan City barangay, and a nearby landmark in the booking form. No GPS or location sharing is required.'],
['Do you repair aircon units?','IcyBreeze focuses on cleaning. If our team identifies a likely repair issue, it will be documented so you can arrange the appropriate repair service.']
] as [$question,$answer])
<div class="faq-item" data-faq-item><button class="faq-button" type="button" data-faq-button aria-expanded="false"><span>{{ $question }}</span><i class="ph ph-plus"></i></button><div class="faq-answer"><p>{{ $answer }}</p></div></div>
@endforeach
</div></section>
@endsection
