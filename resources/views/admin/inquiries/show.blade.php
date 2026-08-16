<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0">Inquiry #{{ $inquiry->reference ?? 'INQ-XXXX' }}</h2>
            <a href="{{ route('admin.inquiries.index') }}" class="btn btn-secondary">
                Back
            </a>
        </div>
    </x-slot>

    <div class="container py-4">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $subject = '[' . ($inquiry->reference ?? 'INQ-XXXX') . '] ' . ($inquiry->tour->title ?? 'Travel Inquiry');
            $mailtoUrl = 'mailto:' . rawurlencode($inquiry->email) . '?subject=' . rawurlencode($subject);
            
            $badgeClass = match($inquiry->status) {
                'new' => 'bg-danger',
                'in_progress' => 'bg-warning text-dark',
                'confirmed' => 'bg-success',
                'unavailable' => 'bg-danger',
                'not_booked' => 'bg-secondary',
                default => 'bg-info',
            };
        @endphp

        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-body p-4">
                
                {{-- Customer Information --}}
                <div class="mb-4">
                    <h5 class="text-primary fw-bold mb-3" style="font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                        Customer Information
                    </h5>
                    <div class="row g-3">
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block small">Name:</span>
                            <strong class="fs-6 text-dark">{{ $inquiry->customer_name }}</strong>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block small">Nationality:</span>
                            <strong class="fs-6 text-dark">{{ $inquiry->nationality ?? '-' }}</strong>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block small">Email:</span>
                            <strong class="fs-6 text-dark">
                                <a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a>
                            </strong>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <span class="text-muted d-block small">Phone:</span>
                            <strong class="fs-6 text-dark">{{ $inquiry->phone ?? '-' }}</strong>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Tour / Request Details --}}
                <div class="mb-4">
                    <h5 class="text-primary fw-bold mb-3" style="font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                        Tour / Request Details
                    </h5>
                    <div class="row g-3">
                        <div class="col-sm-12">
                            <span class="text-muted d-block small">Tour:</span>
                            <strong class="fs-6 text-dark">
                                {{ $inquiry->tour->title ?? 'Custom Tour' }}
                            </strong>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <span class="text-muted d-block small">Travel Date:</span>
                            <strong class="fs-6 text-dark">
                                @if($inquiry->checkin_date)
                                    {{ \Carbon\Carbon::parse($inquiry->checkin_date)->format('d M Y') }}
                                    @if($inquiry->checkout_date)
                                        → {{ \Carbon\Carbon::parse($inquiry->checkout_date)->format('d M Y') }}
                                    @endif
                                @else
                                    Flexible
                                @endif
                            </strong>
                        </div>
                        <div class="col-sm-3 col-md-2">
                            <span class="text-muted d-block small">Adults:</span>
                            <strong class="fs-6 text-dark">{{ $inquiry->number_of_adults }}</strong>
                        </div>
                        <div class="col-sm-3 col-md-2">
                            <span class="text-muted d-block small">Children:</span>
                            <strong class="fs-6 text-dark">{{ $inquiry->number_of_children }}</strong>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Customer Inquiry Message --}}
                <div class="mb-4">
                    <h5 class="text-primary fw-bold mb-3" style="font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px;">
                        Customer Inquiry Message
                    </h5>
                    <div class="border rounded p-3 bg-light text-dark mb-4" style="white-space: pre-line; line-height: 1.6; border-radius: 8px;">
                        {{ $inquiry->message }}
                    </div>
                </div>

                <hr class="my-4">

                {{-- Status & Email Communication Section (Directly under message) --}}
                <form action="{{ route('admin.inquiries.update', $inquiry->id) }}" method="POST" id="status-update-form">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="inquiry-status-select" class="form-label text-muted fw-bold small text-uppercase mb-2">Inquiry Status</label>
                        <div class="d-flex align-items-center gap-3">
                            <select name="status" id="inquiry-status-select" class="form-select border-2" style="max-width: 250px;">
                                <option value="new" {{ $inquiry->status === 'new' ? 'selected' : '' }}>NEW</option>
                                <option value="in_progress" {{ $inquiry->status === 'in_progress' ? 'selected' : '' }}>IN PROGRESS</option>
                                <option value="confirmed" {{ $inquiry->status === 'confirmed' ? 'selected' : '' }}>CONFIRMED</option>
                                <option value="unavailable" {{ $inquiry->status === 'unavailable' ? 'selected' : '' }}>UNAVAILABLE</option>
                                <option value="not_booked" {{ $inquiry->status === 'not_booked' ? 'selected' : '' }}>NOT BOOKED</option>
                            </select>
                        
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="text-muted d-block small text-uppercase fw-bold mb-1">Customer Email:</span>
                        <a href="mailto:{{ $inquiry->email }}" class="fs-6 fw-bold text-decoration-none">{{ $inquiry->email }}</a>
                        
                        <p class="text-muted small mt-2 mb-3">
                            Click below to compose a direct reply. This will open your computer's default email program with the correct recipient and inquiry subject template.
                        </p>
                        
                        <a href="{{ $mailtoUrl }}" class="btn btn-outline-primary py-2.5 px-4 fw-bold rounded-3">
                            <i class="bi bi-envelope-at-fill me-2"></i>Reply via Email
                        </a>
                    </div>
                </form>

                <hr class="my-4">

                {{-- Action Buttons at the Bottom --}}
                <div class="d-flex gap-3 mt-4">
                    <button type="submit" form="status-update-form" class="btn btn-primary px-5 py-2.5 fw-bold rounded-3">
                        <i class="bi bi-check-circle me-1"></i> Save Changes
                    </button>

                    <form action="{{ route('admin.inquiries.destroy', $inquiry->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this inquiry? This cannot be undone.');" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger px-4 py-2.5 fw-bold rounded-3">
                            <i class="bi bi-trash me-1"></i>Delete Inquiry
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>