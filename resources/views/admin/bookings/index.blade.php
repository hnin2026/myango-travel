<x-app-layout>

<div class="container">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Booking Management</h2>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Ref Code</th>
                        <th>Customer</th>
                        <th>Tour</th>
                        <th>Travel Date</th>
                        <th>Persons</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Booked At</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->id }}</td>
                            <td>{{ $booking->ref_code }}</td>
                            <td>{{ $booking->customer_name }}</td>
                            <td>{{ $booking->tour?->title ?? 'No Tour Found' }}</td>
                            <td>{{ $booking->checkin_date }} → {{ $booking->checkout_date }}</td>
                            <td>{{ $booking->num_persons }}</td>
                            <td>${{ $booking->total_price }}</td>
                            <td>{{ ucfirst($booking->status) }}</td>
                            <td>{{ $booking->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.bookings.show', $booking) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">
                                No bookings found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

</div>

</x-app-layout>