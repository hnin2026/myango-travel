<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
            <div>
                <h1 class="page-title mb-1">Booking Management</h1>
            </div>
            <form action="{{ route('admin.bookings.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" 
                       name="search" 
                       class="form-control" 
                       placeholder="Search booking..." 
                       value="{{ request('search') }}"
                       style="width: 250px; height: 40px;">
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
                                <td>{{ $booking->num_persons }}</td>
                                <td>${{ $booking->total_price }}</td>
                                <td>{{ ucfirst($booking->status) }}</td>
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