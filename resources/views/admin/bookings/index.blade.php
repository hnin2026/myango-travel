<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
            <div>
                <h1 class="page-title mb-1">Booking Management</h1>
            </div>
            <form action="{{ route('admin.bookings.index') }}" method="GET" class="d-flex gap-2">
                <select name="status" class="form-select" style="width: 170px; height: 40px;" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="payment_uploaded" {{ request('status') === 'payment_uploaded' ? 'selected' : '' }}>Payment Uploaded</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search booking..." 
                       value="{{ request('search') }}"
                       style="width: 220px; height: 40px;">
                <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center" aria-label="Search" style="height: 40px; width: 40px;">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </x-slot>

<div class="container py-4">

    <div class="card">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>No.</th>
                            <th>Ref Code</th>
                            <th>Customer</th>
                            <th>Tour Name</th>
                            <th>No. of Persons</th>
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
                                <td>
                                    <div>Total: {{ $booking->num_persons + $booking->num_children }}</div>
                                    <small class="text-muted d-block">Adults: {{ $booking->num_persons }}</small>
                                    <small class="text-muted d-block">Children: {{ $booking->num_children }}</small>
                                </td>
                                <td>${{ $booking->total_price }}</td>
                                <td>
                                    <span class="badge bg-{{ $booking->is_payment_uploaded ? 'primary' : ($booking->status === 'paid' || $booking->status === 'confirmed' ? 'success' : ($booking->status === 'completed' ? 'info' : ($booking->status === 'cancelled' ? 'danger' : 'warning'))) }}">
                                        {{ $booking->display_status_label }}
                                    </span>
                                </td>
                                <td>{{ $booking->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $booking) }}"
                                       class="btn btn-sm btn-primary">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    No bookings found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            <div class="mt-4">
                {{ $bookings->links() }}
            </div>

        </div>
    </div>

</div>

</x-app-layout>