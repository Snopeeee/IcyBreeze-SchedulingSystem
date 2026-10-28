@extends('layouts.site')
@php($title = 'Frequently Asked Questions')
@section('content')
<section class="page-hero"><div class="site-shell page-intro"><span class="eyebrow">Customer information</span><h1>Frequently Asked Questions</h1><p>Review information about scheduling, service preparation, payments, and future maintenance options.</p></div></section>
<section class="section"><div class="site-shell faq-list">
@foreach($faqs as $faq)
<div class="faq-item" data-faq-item><button class="faq-button" type="button" data-faq-button aria-expanded="false"><span>{{ $faq->question }}</span><i class="ph ph-plus"></i></button><div class="faq-answer"><p>{{ $faq->answer }}</p></div></div>
@endforeach
</div></section>
@endsection
