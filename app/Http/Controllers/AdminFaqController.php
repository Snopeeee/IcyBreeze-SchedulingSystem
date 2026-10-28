<?php

namespace App\Http\Controllers;

use App\Models\FaqEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminFaqController extends Controller
{
    public function index(): View
    {
        return view('admin.faqs.index', ['faqs' => FaqEntry::orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create(): View
    {
        return view('admin.faqs.form', ['faq' => new FaqEntry]);
    }

    public function store(Request $request): RedirectResponse
    {
        FaqEntry::create($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ added.');
    }

    public function edit(FaqEntry $faq): View
    {
        return view('admin.faqs.form', compact('faq'));
    }

    public function update(Request $request, FaqEntry $faq): RedirectResponse
    {
        $faq->update($this->validated($request));

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated.');
    }

    public function destroy(FaqEntry $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ removed.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:3000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
        ]);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
