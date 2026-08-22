<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">
                Available Dates for {{ $tour->title }}
            </h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.tours.travel-periods.create', $tour) }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Date
                </a>
                <a href="{{ route('admin.tours.index') }}" class="btn btn-secondary">
                    Back
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

        {{-- Operating Period Card --}}
        <div class="card mb-4">
            <div class="card-header fw-bold">Tour Operating Period</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Earliest Departure (Start Date):</strong>
                        <span class="ms-2 text-primary fw-bold">
                            {{ $tour->travelPeriods->min('start_date')?->format('d M Y') ?? 'Not configured' }}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong>Latest Departure (End Date):</strong>
                        <span class="ms-2 text-primary fw-bold">
                            {{ $tour->travelPeriods->max('end_date')?->format('d M Y') ?? 'Not configured' }}
                        </span>
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
                                    <a href="{{ route('admin.tours.travel-periods.edit', [$tour, $date]) }}"
                                       class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.tours.travel-periods.destroy', [$tour, $date]) }}"
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
                                    <a href="{{ route('admin.tours.travel-periods.create', $tour) }}">Add your first date!</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Blackout Periods Card --}}
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
                <span class="fw-bold"><i class="bi bi-calendar-x me-2"></i>Blackout Periods</span>
                <a href="{{ route('admin.tours.blackouts.create', $tour) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Add Blackout Period
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tour->blackoutPeriods as $blackout)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $blackout->start_date->format('d M Y') }}</td>
                                    <td>{{ $blackout->end_date->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.tours.blackouts.edit', [$tour, $blackout]) }}"
                                           class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="{{ route('admin.tours.blackouts.destroy', [$tour, $blackout]) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this blackout period?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i> Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No blackout periods added yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Individual Departure Dates Overview --}}
        <div class="card mt-4 mb-4">
            <div class="card-header fw-bold bg-secondary text-white">
                <i class="bi bi-list-check me-2"></i>Individual Departure Dates Overview
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total Seats</th>
                                <th>Booked</th>
                                <th>Available</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $departureDates = $tour->getAvailableDepartureDates();
                            @endphp
                            @forelse($departureDates as $dateStr => $info)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($dateStr)->format('d M Y') }}</td>
                                    <td>{{ $info['status'] === 'unavailable' ? '-' : $info['travel_period']->total_seats }}</td>
                                    <td>{{ $info['status'] === 'unavailable' ? '-' : $info['travel_period']->booked_seats }}</td>
                                    <td>{{ $info['available_seats'] }}</td>
                                    <td>
                                        @if($info['status'] === 'available')
                                            <span class="badge bg-success">Available</span>
                                        @elseif($info['status'] === 'full')
                                            <span class="badge bg-danger">Fully Booked</span>
                                        @else
                                            <span class="badge bg-secondary">Unavailable (Blackout)</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        No departure dates available. Please configure travel periods.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>