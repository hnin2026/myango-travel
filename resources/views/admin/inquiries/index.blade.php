<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100 flex-wrap gap-3">
            <div>
                <h1 class="page-title mb-1">Customer Inquiries</h1>
            </div>
            <form action="{{ route('admin.inquiries.index') }}" method="GET" class="d-flex gap-2">
                <select name="status" class="form-select" style="width: 250px; height: 40px; padding-top: 0; padding-bottom: 0;">
                    <option value="">All Statuses</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>New</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="unavailable" {{ request('status') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                    <option value="not_booked" {{ request('status') === 'not_booked' ? 'selected' : '' }}>Not Booked</option>
                </select>
                <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center" aria-label="Search" style="height: 40px; width: 40px;">
                    <i class="bi bi-search"></i>
                </button>
            </form>
        </div>
    </x-slot>

    <div class="container py-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif



        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead class="table-dark">
                            <tr>
                                <th>No.</th>
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
                            @forelse($inquiries as $inquiry)
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
                                    <td>{{ $inquiry->id }}</td>
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
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        No inquiries found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                <div class="mt-4">
                    {{ $inquiries->links() }}
                </div>
            </div>
        </div>

    </div>
</x-app-layout>