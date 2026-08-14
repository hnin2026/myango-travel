<x-app-layout>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 fw-bold" style="color: #111844;">Booking Detail</h2>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-sm">
            Back
        </a>
    </div>

    <div class="row g-4">
        <!-- Customer & General Info Card -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100 mb-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4" style="color: #111844;"><i class="bi bi-person-fill me-2"></i>Customer & Billing Information</h5>
                    
                    <div class="mb-4">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Booking Reference</label>
                        <span class="fs-4 fw-bold text-primary">{{ $booking->ref_code }}</span>
                    </div>
                    
                    <hr class="text-muted opacity-25">
                    
                    <div class="row g-3">
                        <div class="col-sm-6 col-12">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Customer Name</label>
                            <span class="fw-semibold text-dark">{{ $booking->customer_name }}</span>
                        </div>
                        <div class="col-sm-6 col-12">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Nationality</label>
                            <span class="text-dark">{{ $booking->nationality ?? '-' }}</span>
                        </div>
                        <div class="col-sm-6 col-12">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Email Address</label>
                            <span><a href="mailto:{{ $booking->email }}" class="text-decoration-none fw-medium">{{ $booking->email }}</a></span>
                        </div>
                        <div class="col-sm-6 col-12">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Phone Number</label>
                            <span class="text-dark">{{ $booking->phone }}</span>
                        </div>
                    </div>

                    @if($booking->message)
                        <hr class="text-muted opacity-25">
                        <div class="mb-0">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-2">Special Request / Message</label>
                            <div class="p-3 bg-light rounded text-muted small" style="white-space: pre-wrap; line-height: 1.6;">{{ $booking->message }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Tour & Travel Info Card -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100 mb-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4" style="color: #111844;"><i class="bi bi-geo-alt-fill me-2"></i>Tour & Reservation Details</h5>
                    
                    <div class="mb-4">
                        <label class="text-muted small fw-bold text-uppercase d-block mb-1">Tour Title</label>
                        <span class="fs-5 fw-bold text-dark">{{ $booking->tour?->title }}</span>
                    </div>
                    
                    <hr class="text-muted opacity-25">
                    
                    <div class="row g-3">
                        <div class="col-sm-6 col-12">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Hotel Selection</label>
                            <span class="text-dark">{{ $booking->hotel?->name }} ({{ $booking->hotel?->category }})</span>
                        </div>
                        <div class="col-sm-6 col-12">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Travel Period</label>
                            <span class="text-dark">{{ $booking->travelPeriod?->start_date }} &rarr; {{ $booking->travelPeriod?->end_date }}</span>
                        </div>
                        <div class="col-sm-6 col-12">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Check-in Date</label>
                            <span class="text-dark">{{ $booking->checkin_date }}</span>
                        </div>
                        <div class="col-sm-6 col-12">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Check-out Date</label>
                            <span class="text-dark">{{ $booking->checkout_date }}</span>
                        </div>
                        <div class="col-sm-4 col-4">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Adults</label>
                            <span class="fw-semibold text-dark">{{ $booking->num_persons }} pax</span>
                        </div>
                        <div class="col-sm-4 col-4">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Children</label>
                            <span class="fw-semibold text-dark">{{ $booking->num_children }} pax</span>
                        </div>
                        <div class="col-sm-4 col-4">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Child Ages</label>
                            <span class="text-dark">{{ $booking->child_ages ?? '-' }}</span>
                        </div>
                    </div>
                    
                    <hr class="text-muted opacity-25">
                    
                    <div class="row g-3 align-items-center">
                        <div class="col-sm-6 col-12">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Total Pricing</label>
                            <span class="fs-3 fw-bold text-success">${{ number_format($booking->total_price, 2) }}</span>
                        </div>
                        <div class="col-sm-6 col-12">
                            <label class="text-muted small fw-bold text-uppercase d-block mb-1">Booking Status</label>
                            <span class="badge bg-{{ $booking->status == 'confirmed' || $booking->status == 'paid' ? 'success' : ($booking->status == 'cancelled' ? 'danger' : 'warning') }} fs-6">
                                {{ ucfirst($booking->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment and Cancellation Actions Card -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    @if($booking->payment_receipt)
                        <h5 class="card-title fw-bold mb-3" style="color: #111844;"><i class="bi bi-credit-card-2-front-fill me-2"></i>Payment Receipt Verification</h5>
                        <div class="p-3 bg-light border rounded mb-4">
                            <p class="text-muted small mb-3">Receipt uploaded on: <strong>{{ $booking->payment_uploaded_at?->format('F d, Y \a\t H:i:s') ?? 'N/A' }}</strong></p>
                            <div class="mb-3">
                                @if(Str::endsWith($booking->payment_receipt, '.pdf'))
                                    <a href="{{ asset('storage/' . $booking->payment_receipt) }}" target="_blank" class="btn btn-outline-primary btn-sm">
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
                    @endif

                    @if($booking->status === 'cancelled')
                        <div class="alert alert-danger mb-0">
                            <h5 class="alert-heading fw-bold"><i class="bi bi-x-circle-fill me-2"></i>Booking Cancelled</h5>
                            <p class="mb-1"><strong>Cancelled By:</strong> {{ ucfirst($booking->cancelled_by ?? 'System') }}</p>
                            <p class="mb-1"><strong>Cancelled At:</strong> {{ $booking->cancelled_at ? $booking->cancelled_at->format('F d, Y H:i:s') : 'N/A' }}</p>
                            <p class="mb-0"><strong>Reason:</strong> {{ $booking->cancel_reason ?? 'No reason specified' }}</p>
                        </div>
                    @endif

                    @if($booking->status !== 'cancelled')
                        <h5 class="card-title fw-bold mb-3" style="color: #111844;"><i class="bi bi-sliders me-2"></i>Administrative Actions</h5>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            @if($booking->status == 'pending')
                                <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-success">
                                        Confirm Booking
                                    </button>
                                </form>
                            @endif

                            @if($booking->status == 'confirmed')
                                <form action="{{ route('admin.bookings.update', $booking) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="paid">
                                    <button type="submit" class="btn btn-primary">
                                        Mark as Paid
                                    </button>
                                </form>
                            @endif

                            @if($booking->status == 'payment_uploaded')
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
                            @endif

                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelBookingModal">
                                Cancel Booking
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
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
</div>

</x-app-layout>