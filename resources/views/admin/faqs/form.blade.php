@extends('layouts.admin')
@php($title = $faq->exists ? 'Edit FAQ' : 'Add FAQ')
@section('content')
<div class="admin-top"><div><a class="text-link" href="{{ route('admin.faqs.index') }}">← Website FAQs</a><h1>{{ $faq->exists ? 'Edit FAQ' : 'Add FAQ' }}</h1><p>Keep answers accurate, concise, and useful to customers.</p></div></div>
<section class="admin-card faq-form-card"><form class="admin-form" method="POST" action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}">@csrf @if($faq->exists)@method('PUT')@endif
<div><label for="question">Question</label><input id="question" name="question" value="{{ old('question', $faq->question) }}" required maxlength="255"></div>
<div><label for="answer">Answer</label><textarea id="answer" name="answer" rows="7" required maxlength="3000">{{ old('answer', $faq->answer) }}</textarea></div>
<div><label for="sort_order">Display order</label><input id="sort_order" type="number" name="sort_order" min="0" max="999" value="{{ old('sort_order', $faq->sort_order ?? 0) }}" required></div>
<label class="login-remember"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $faq->exists ? $faq->is_published : true))> Publish on website</label>
<button class="button button-navy" type="submit">Save FAQ</button>
</form></section>
@endsection
