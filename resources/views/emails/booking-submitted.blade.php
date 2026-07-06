<h2>Booking Request Received</h2>

<p>Dear {{ $booking->customer_name }},</p>

<p>Your booking request has been received successfully.</p>

<p><strong>Reference Code:</strong> {{ $booking->ref_code }}</p>
<p><strong>Tour:</strong> {{ $booking->tour?->title }}</p>
<p><strong>Travel Date:</strong> {{ $booking->checkin_date }} → {{ $booking->checkout_date }}</p>
<p><strong>Total Price:</strong> ${{ $booking->total_price }}</p>

<p>Our staff will review your booking and contact you soon.</p>

<p>Thank you for choosing MyanGo Travel.</p>