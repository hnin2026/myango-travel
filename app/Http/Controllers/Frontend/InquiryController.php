<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'tour_id'       => 'required|exists:tours,id',
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

            Inquiry::create([
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

        return back()->with(
            'inquiry_success',
            'Inquiry sent successfully!'
        );
    }
}