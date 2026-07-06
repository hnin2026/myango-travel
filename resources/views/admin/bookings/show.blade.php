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
            @if($booking->status == 'pending')

                <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" class="d-flex gap-2">

                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="status" value="confirmed">

                    <button type="submit" class="btn btn-success">
                        Confirm Booking
                    </button>
                </form>

                <form action="{{ route('admin.bookings.update', $booking) }}" method="POST" class="mt-2">

                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="status" value="cancelled">

                    <button type="submit" class="btn btn-danger">
                        Cancel Booking
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
            @if($booking->message)
                <hr>
                <p><strong>Special Request:</strong></p>
                <p>{{ $booking->message }}</p>
            @endif

        </div>
    </div>

</div>

</x-app-layout>