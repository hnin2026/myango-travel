<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $query = Inquiry::latest();

        if ($request->filled('status') && in_array($request->status, ['new', 'in_progress', 'confirmed', 'unavailable', 'not_booked'])) {
            $query->where('status', $request->status);
        }

        $inquiries = $query->paginate(10)->withQueryString();

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

    public function update(Request $request, Inquiry $inquiry)
    {
        $request->validate([
            'status' => 'required|in:new,in_progress,confirmed,unavailable,not_booked',
        ]);

        $inquiry->update([
            'status' => $request->status,
        ]);

        return redirect()
            ->route('admin.inquiries.show', $inquiry)
            ->with('success', 'Inquiry status updated successfully.');
    }

    public function destroy(Inquiry $inquiry)
    {
        $inquiry->delete();

        return redirect()
            ->route('admin.inquiries.index')
            ->with('success', 'Inquiry deleted successfully.');
    }
}