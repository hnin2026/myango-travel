<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">
                Available Dates for {{ $tour->title }}
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.tours.dates.create', $tour) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Date
                </a>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Tours
                </a>
            </div>
        </div>
    </x-slot>

    <div class="container py-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Tour Info --}}
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted">Tour</small>
                        <p class="fw-bold mb-0">{{ $tour->title }}</p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Base Price</small>
                        <p class="fw-bold mb-0">${{ number_format($tour->base_price, 2) }}</p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Duration</small>
                        <p class="fw-bold mb-0">{{ $tour->duration_days }} days</p>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Location</small>
                        <p class="fw-bold mb-0">{{ $tour->location }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Dates Table --}}
        <div class="card">
            <div class="card-body">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Season</th>
                            <th>Total Seats</th>
                            <th>Booked</th>
                            <th>Available</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dates as $date)
                            @php
                                $season = \App\Models\SeasonPeriod::getSeasonForDate($date->start_date);
                                $available = $date->availableSeats();
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $date->start_date->format('d M Y') }} - {{ $date->end_date->format('d M Y') }}</td>
                                <td>
                                    @if($season === 'peak')
                                        <span class="badge bg-danger">Peak</span>
                                    @elseif($season === 'normal')
                                        <span class="badge bg-warning text-dark">Normal</span>
                                    @else
                                        <span class="badge bg-success">Low</span>
                                    @endif
                                </td>
                                <td>{{ $date->total_seats }}</td>
                                <td>{{ $date->booked_seats }}</td>
                                <td>{{ $available }}</td>
                                <td>
                                    @if($available > 0)
                                        <span class="badge bg-success">Available</span>
                                    @else
                                        <span class="badge bg-danger">Full</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.tours.dates.edit', [$tour, $date]) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.tours.dates.destroy', [$tour, $date]) }}"
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No dates added yet.
                                    <a href="{{ route('admin.tours.dates.create', $tour) }}">Add your first date!</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>