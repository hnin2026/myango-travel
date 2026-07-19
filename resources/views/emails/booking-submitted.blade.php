<h2>Booking Request Received</h2>

<p>Dear {{ $booking->customer_name }},</p>

<p>Your booking request has been received successfully.</p>

<p><strong>Reference Code:</strong> {{ $booking->ref_code }}</p>
<p><strong>Tour:</strong> {{ $booking->tour?->title }}</p>
<p><strong>Travel Date:</strong> {{ $booking->checkin_date }} → {{ $booking->checkout_date }}</p>
<p><strong>Total Price:</strong> ${{ $booking->total_price }}</p>

<p>Our staff will review your booking and contact you soon.</p>

<div style="margin: 30px 0;">
    <a href="{{ url('/booking/cancel/' . $booking->cancellation_token) }}" style="display: inline-block; background-color: #c0392b; color: #ffffff !important; text-decoration: none; padding: 12px 24px; font-size: 15px; font-weight: bold; border-radius: 8px;" target="_blank">
        Cancel Booking
    </a>
</div>

<p>Thank you for choosing MyanGo Travel.</p>