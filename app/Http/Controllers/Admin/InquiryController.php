<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;

class InquiryController extends Controller
{
    public function index()
    {
        $inquiries = Inquiry::latest()->get();

        return view(
            'admin.inquiries.index',
            compact('inquiries')
        );
    }

    public function show(Inquiry $inquiry)
    {
        return view(
            'admin.inquiries.show',
            compact('inquiry')
        );
    }

    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();

        return redirect()
            ->route('admin.inquiries.index')
            ->with('success', 'Inquiry deleted successfully.');
    }
}