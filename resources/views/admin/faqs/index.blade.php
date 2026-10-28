@extends('layouts.admin')
@php($title = 'Website FAQs')
@section('content')
<div class="admin-top"><div><span class="admin-eyebrow">Website content</span><h1>Frequently Asked Questions</h1><p>Create, review, update, publish, and remove customer-facing answers.</p></div><a class="button button-yellow button-small" href="{{ route('admin.faqs.create') }}">Add FAQ</a></div>
<section class="admin-card"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Question</th><th>Visibility</th><th>Order</th><th>Actions</th></tr></thead><tbody>
@forelse($faqs as $faq)
<tr><td><strong>{{ $faq->question }}</strong><span>{{ Str::limit($faq->answer, 100) }}</span></td><td><span class="status-badge {{ $faq->is_published ? 'status-paid' : 'status-pending' }}">{{ $faq->is_published ? 'Published' : 'Hidden' }}</span></td><td>{{ $faq->sort_order }}</td><td><div class="faq-actions"><a class="text-link" href="{{ route('admin.faqs.edit', $faq) }}">Edit</a><form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" onsubmit="return confirm('Remove this FAQ?')">@csrf @method('DELETE')<button class="text-link" type="submit">Delete</button></form></div></td></tr>
@empty <tr><td colspan="4">No FAQs yet.</td></tr>
@endforelse
</tbody></table></div></section>
@endsection
