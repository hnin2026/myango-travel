<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

use App\Mail\AdminInquirySubmittedMail;
use Illuminate\Support\Facades\Mail;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tour_id'       => 'nullable|exists:tours,id',
            'customer_name' => 'required|string|max:255',
            'nationality'   => 'nullable|string|max:255',
            'phone'         => 'nullable|string|max:255',
            'email'         => 'required|email|max:255',
            'number_of_adults' => 'required|integer|min:1',
            'number_of_children' => 'nullable|integer|min:0',
            'checkin_date'  => 'nullable|date',
            'checkout_date' => 'nullable|date',
            'message'       => 'nullable|string',
        ]);

        $inquiry = Inquiry::create([
            'tour_id'       => $request->tour_id,
            'customer_name' => $request->customer_name,
            'nationality'   => $request->nationality,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'number_of_adults' => $request->number_of_adults,
            'number_of_children' => $request->number_of_children ?? 0,
            'checkin_date'  => $request->checkin_date,
            'checkout_date' => $request->checkout_date,
            'message'       => $request->message,
            'status'        => 'new',
        ]);

        try {
            $adminEmail = env('ADMIN_NOTIFICATION_EMAIL') ?: config('mail.from.address') ?: 'admin@myango.com';
            Mail::to($adminEmail)->send(new AdminInquirySubmittedMail($inquiry));
        } catch (\Exception $e) {
            // Log or ignore email sending errors in local environment to prevent crash
            logger()->error('Failed sending admin inquiry email: ' . $e->getMessage());
        }

        return redirect()->route('inquiry.success', $inquiry->id);
    }

    public function success(Inquiry $inquiry)
    {
        return view('frontend.inquiry.success', compact('inquiry'));
    }
}