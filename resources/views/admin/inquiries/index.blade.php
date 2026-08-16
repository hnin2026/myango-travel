<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div>
                <h1 class="page-title mb-1">Customer Inquiries</h1>
            </div>
        </div>
    </x-slot>

    <div class="container py-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter Buttons --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body py-3">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="text-muted fw-bold me-2" style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Filter Status:</span>
                    <a href="{{ route('admin.inquiries.index') }}" class="btn btn-sm rounded-pill px-3 {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">All</a>
                    <a href="{{ route('admin.inquiries.index', ['status' => 'new']) }}" class="btn btn-sm rounded-pill px-3 {{ request('status') === 'new' ? 'btn-danger' : 'btn-outline-secondary' }}">New</a>
                    <a href="{{ route('admin.inquiries.index', ['status' => 'in_progress']) }}" class="btn btn-sm rounded-pill px-3 {{ request('status') === 'in_progress' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">In Progress</a>
                    <a href="{{ route('admin.inquiries.index', ['status' => 'confirmed']) }}" class="btn btn-sm rounded-pill px-3 {{ request('status') === 'confirmed' ? 'btn-success' : 'btn-outline-secondary' }}">Confirmed</a>
                    <a href="{{ route('admin.inquiries.index', ['status' => 'unavailable']) }}" class="btn btn-sm rounded-pill px-3 {{ request('status') === 'unavailable' ? 'btn-danger' : 'btn-outline-secondary' }}">Unavailable</a>
                    <a href="{{ route('admin.inquiries.index', ['status' => 'not_booked']) }}" class="btn btn-sm rounded-pill px-3 {{ request('status') === 'not_booked' ? 'btn-secondary' : 'btn-outline-secondary' }}">Not Booked</a>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-0">

                @if($inquiries->count() > 0)

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">

                            <thead class="table-dark">
                                <tr>
                                    <th>Reference</th>
                                    <th>Customer</th>
                                    <th>Tour / Request</th>
                                    <th>Travel Date</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($inquiries as $inquiry)
                                    @php
                                        $badgeClass = match($inquiry->status) {
                                            'new' => 'bg-danger',
                                            'in_progress' => 'bg-warning text-dark',
                                            'confirmed' => 'bg-success',
                                            'unavailable' => 'bg-danger',
                                            'not_booked' => 'bg-secondary',
                                            default => 'bg-info',
                                        };
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="font-monospace fw-bold text-dark">{{ $inquiry->reference ?? 'INQ-XXXX' }}</span>
                                        </td>

                                        <td>
                                            <strong>{{ $inquiry->customer_name }}</strong>
                                            @if($inquiry->nationality)
                                                <div class="text-muted small">{{ $inquiry->nationality }}</div>
                                            @endif
                                        </td>

                                        <td>
                                            @if($inquiry->tour)
                                                <span class="text-primary fw-medium">{{ $inquiry->tour->title }}</span>
                                            @else
                                                <span class="text-muted italic">Custom Tour</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if($inquiry->checkin_date)
                                                <span style="font-size: 13px;">
                                                    {{ \Carbon\Carbon::parse($inquiry->checkin_date)->format('d M Y') }}
                                                    @if($inquiry->checkout_date)
                                                        <br><span class="text-muted">→ {{ \Carbon\Carbon::parse($inquiry->checkout_date)->format('d M Y') }}</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-muted">Flexible</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge {{ $badgeClass }} text-uppercase px-2.5 py-1.5" style="font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">
                                                {{ $inquiry->status_label }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="text-muted small">{{ $inquiry->created_at->format('d M Y') }}</span>
                                        </td>

                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.inquiries.show', $inquiry->id) }}"
                                                   class="btn btn-sm btn-primary">
                                                    View
                                                </a>
                                                <form action="{{ route('admin.inquiries.destroy', $inquiry->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this inquiry?');" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                @else

                    <div class="text-center py-5">
                        <i class="bi bi-chat-left-text text-muted mb-3" style="font-size: 48px; display: block;"></i>
                        <h5>No inquiries found</h5>
                        <p class="text-muted mb-0">
                            Customer inquiries matching the status will appear here.
                        </p>
                    </div>

                @endif

            </div>
        </div>

    </div>
</x-app-layout>