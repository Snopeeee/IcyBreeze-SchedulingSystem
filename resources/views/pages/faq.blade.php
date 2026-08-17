@extends('layouts.site')
@php($title = 'Frequently Asked Questions')
@section('content')
<section class="page-hero"><div class="site-shell page-intro"><span class="eyebrow">Good to know</span><h1>Frequently asked questions</h1><p>Helpful answers about scheduling, cleaning, preparation, and payment.</p></div></section>
<section class="section"><div class="site-shell faq-list">
@foreach([
['How often should my aircon be cleaned?','For regular home use, professional cleaning every three to six months is a practical starting point. Heavy daily use, pets, construction dust, or weaker airflow can mean more frequent care.'],
['How long does one appointment take?','A standard cleaning usually takes around 75 minutes per unit, while deep cleaning takes around two hours. Additional units add time to the appointment.'],
['What should I prepare before the technician arrives?','Please clear a comfortable work area below and around the aircon, secure pets, and make sure electricity and water are available.'],
['Can I cancel or change my booking?','Your private manage-booking link lets you review the appointment. Online cancellation is available until 24 hours before the visit; closer changes need a quick call.'],
['How can I pay?','You may select cash after service. The online-payment choice in this MVP is a safe demo workflow and will require live PayMongo credentials before production use.'],
['How do recurring care plans work?','Choose quarterly or biannual care, a preferred first date, and your usual service. The office confirms each upcoming visit and you pay per completed visit in this MVP.'],
['Why can I share my GPS location?','The location button is optional. If you consent, the precise pin is saved with the service address so only the office and assigned technician can find you and plan the route more accurately.'],
['Do you repair aircon units?','This MVP focuses on cleaning. If the technician identifies a likely repair issue, it will be documented so you can arrange the appropriate repair service.']
] as [$question,$answer])
<div class="faq-item" data-faq-item><button class="faq-button" type="button" data-faq-button aria-expanded="false"><span>{{ $question }}</span><i class="ph ph-plus"></i></button><div class="faq-answer"><p>{{ $answer }}</p></div></div>
@endforeach
</div></section>
@endsection
