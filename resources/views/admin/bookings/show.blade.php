<x-app-layout>

<div class="container">

    <h2 class="mb-4">Booking Detail</h2>

    <div class="card shadow-sm">
        <div class="card-body">

            <p><strong>Reference:</strong> {{ $booking->ref_code }}</p>
            <p><strong>Customer:</strong> {{ $booking->customer_name }}</p>
            <p><strong>Email:</strong> {{ $booking->email }}</p>
            <p><strong>Phone:</strong> {{ $booking->phone }}</p>
            <p><strong>Nationality:</strong> {{ $booking->nationality }}</p>

            <hr>

            <p><strong>Tour:</strong> {{ $booking->tour?->title }}</p>
            <p><strong>Hotel:</strong> {{ $booking->hotel?->name }}</p>
            <p><strong>Travel Period:</strong>
                {{ $booking->travelPeriod?->start_date }}
                →
                {{ $booking->travelPeriod?->end_date }}
            </p>

            <hr>

            <p><strong>Check-in:</strong> {{ $booking->checkin_date }}</p>
            <p><strong>Check-out:</strong> {{ $booking->checkout_date }}</p>
            <p><strong>Adults:</strong> {{ $booking->num_persons }}</p>
            <p><strong>Children:</strong> {{ $booking->num_children }}</p>
            <p><strong>Child Ages:</strong> {{ $booking->child_ages }}</p>

            <hr>

            <p><strong>Total Price:</strong> ${{ $booking->total_price }}</p>
            <p><strong>Status:</strong> {{ ucfirst($booking->status) }}</p>
            <hr>
            @if($booking->payment_receipt)
                <div class="mt-4 p-3 bg-light border rounded">
                    <h5>Uploaded Payment Receipt</h5>
                    <p class="text-muted small">Uploaded At: {{ $booking->payment_uploaded_at?->format('Y-m-d H:i:s') }}</p>
                    <div class="mb-3">
                        @if(Str::endsWith($booking->payment_receipt, '.pdf'))
                            <a href="{{ asset('storage/' . $booking->payment_receipt) }}" target="_blank" class="btn btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf"></i> View PDF Receipt
                            </a>
                        @else
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $booking->payment_receipt) }}" alt="Payment Receipt" class="img-thumbnail" style="max-height: 350px; max-width: 100%;">
                            </div>
                            <a href="{{ asset('storage/' . $booking->payment_receipt) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                Open in New Tab
                            </a>
                        @endif
                    </div>
                </div>
                <hr>
            @endif

            @if($booking->status == 'pending')

                <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" class="d-flex gap-2">

                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="status" value="confirmed">

                    <button type="submit" class="btn btn-success">
                        Confirm Booking
                    </button>
                </form>

            @endif


            @if($booking->status == 'confirmed')

                <form action="{{ route('admin.bookings.update', $booking) }}"
                    method="POST"
                    class="mt-2">

                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="status" value="paid">

                    <button type="submit" class="btn btn-primary">
                        Mark as Paid
                    </button>
                </form>

            @endif

            @if($booking->status == 'payment_uploaded')

                <div class="d-flex gap-2 mt-2">
                    <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="paid">
                        <button type="submit" class="btn btn-success">
                            Approve Payment
                        </button>
                    </form>

                    <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="btn btn-danger">
                            Reject Payment
                        </button>
                    </form>
                </div>

            @endif

            @if($booking->status === 'cancelled')
                <hr>
                <div class="alert alert-danger mt-3">
                    <h5 class="alert-heading fw-bold"><i class="bi bi-x-circle-fill me-2"></i>Booking Cancelled</h5>
                    <p class="mb-1"><strong>Cancelled By:</strong> {{ ucfirst($booking->cancelled_by ?? 'System') }}</p>
                    <p class="mb-1"><strong>Cancelled At:</strong> {{ $booking->cancelled_at ? $booking->cancelled_at->format('F d, Y H:i:s') : 'N/A' }}</p>
                    <p class="mb-0"><strong>Reason:</strong> {{ $booking->cancel_reason ?? 'No reason specified' }}</p>
                </div>
            @endif

            @if($booking->status !== 'cancelled')
                <hr>
                <div class="mt-3">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelBookingModal">
                        Cancel Booking
                    </button>
                </div>

                <!-- Cancel Booking Modal -->
                <div class="modal fade" id="cancelBookingModal" tabindex="-1" aria-labelledby="cancelBookingModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold" id="cancelBookingModalLabel">Cancel Booking</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="cancel_reason" class="form-label fw-semibold">Reason for Cancellation <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="4" required placeholder="Please enter the reason for cancellation..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            @if($booking->message)
                <hr>
                <p><strong>Special Request:</strong></p>
                <p>{{ $booking->message }}</p>
            @endif

        </div>
    </div>

</div>

</x-app-layout>