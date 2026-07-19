<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div>
                <h1 class="page-title mb-1">Dashboard</h1>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid px-0 py-3">
        
        {{-- 1. WELCOME GREETING CARD --}}
        <div class="card mb-4 border-0 shadow-sm" style="border-radius: 16px; background: linear-gradient(135deg, var(--navy) 0%, var(--mid-blue) 100%);">
            <div class="card-body p-3 text-white">
                <div class="d-flex flex-sm-row flex-column align-items-sm-center justify-content-between gap-3">
                    <div>
                        <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1 mb-2 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.8px;">
                            {{ auth()->user()->role === 'admin' ? 'Administrator Portal' : 'Staff Console' }}
                        </span>
                        @php
                            $hour = date('H');
                            if ($hour < 12) {
                                $greeting = 'Good morning';
                            } elseif ($hour < 17) {
                                $greeting = 'Good afternoon';
                            } else {
                                $greeting = 'Good evening';
                            }
                        @endphp
                        <h2 class="fw-bold mb-1">{{ $greeting }}, {{ auth()->user()->name }}!</h2>
                        <p class="mb-0 text-white opacity-75" style="font-size: 14px;">
                            @if(auth()->user()->role === 'admin')
                                Welcome back. Here's your MyanGo Travel overview.
                            @else
                                Here's today's booking and operational activity overview.
                            @endif
                        </p>
                    </div>
                    <div class="d-flex flex-column align-items-sm-end align-items-start gap-2 align-self-sm-center align-self-start">
                        @php
                            $pendingBookings = \App\Models\Booking::where('status', 'pending')->latest()->take(5)->get();
                            $paymentBookings = \App\Models\Booking::where('status', 'payment_uploaded')->latest()->take(5)->get();
                            $newInquiries = \App\Models\Inquiry::where('status', 'new')->latest()->take(5)->get();

                            $notiCount = $pendingBookingsCount + $paymentVerificationCount + $newInquiriesCount;
                        @endphp
                        {{-- Controls: Notifications & Settings Dropdown (Above Calendar) --}}
                        <div class="d-flex align-items-center gap-2 mb-1">
                            
                            {{-- 👤 Profile Icon with Hover Info (Left Side of Notification) --}}
                            <div class="position-relative profile-hover-container">
                                <button class="btn btn-link p-2 text-white rounded-circle d-flex align-items-center justify-content-center hover-bg-light-white" type="button" style="width: 38px; height: 38px; text-decoration: none; background: rgba(255,255,255,0.1);">
                                    <i class="bi bi-person-circle fs-5"></i>
                                </button>
                                <div class="profile-tooltip shadow p-3 bg-white text-dark rounded position-absolute text-start" style="top: 44px; right: 0; min-width: 180px; z-index: 1050; border: 1px solid rgba(17,24,68,0.08) !important; display: none;">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi bi-person text-dark fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark" style="font-size: 13px; line-height: 1.2;">{{ auth()->user()->name }}</div>
                                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5" style="font-size: 9px; font-weight: 600;">{{ ucfirst(auth()->user()->role) }}</span>
                                        </div>
                                    </div>
                                    <div class="text-muted" style="font-size: 10px; border-top: 1px solid rgba(17,24,68,0.05); padding-top: 6px;">
                                        {{ auth()->user()->email }}
                                    </div>
                                </div>
                            </div>

                            {{-- Bell / Notification Dropdown --}}
                            <div class="dropdown">
                                <button class="btn btn-link p-2 text-white rounded-circle position-relative d-flex align-items-center justify-content-center hover-bg-light-white" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 38px; height: 38px; text-decoration: none; background: rgba(255,255,255,0.1);" title="Notifications">
                                    <i class="bi bi-bell fs-5"></i>
                                    @if($notiCount > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; padding: 3px 5px !important; margin-top: 6px; margin-left: -6px;">
                                            {{ $notiCount }}
                                        </span>
                                    @endif
                                </button>
                                <div class="dropdown-menu dropdown-menu-end shadow border-0 p-3" style="width: 360px; border-radius: 16px; border: 1px solid rgba(17,24,68,0.08) !important;">
                                    <h6 class="fw-bold mb-3 d-flex align-items-center justify-content-between">
                                        <span class="text-dark">Notifications</span>
                                        @if($notiCount > 0)
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1" style="font-size: 10px;">{{ $notiCount }} Action Required</span>
                                        @endif
                                    </h6>
                                    <div class="d-flex flex-column gap-2" style="max-height: 280px; overflow-y: auto;">
                                        {{-- List Payment Verification Bookings --}}
                                        @foreach($paymentBookings as $booking)
                                            <a href="{{ route('admin.bookings.show', $booking) }}" class="dropdown-item p-2 rounded d-flex align-items-start gap-2 text-wrap" style="transition: 0.2s; border-bottom: 1px solid rgba(17,24,68,0.05);">
                                                <span class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; flex-shrink: 0;"><i class="bi bi-credit-card-2-front"></i></span>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold small text-dark">Payment Verification</div>
                                                    <div class="font-monospace text-muted" style="font-size: 11px;">#{{ $booking->ref_code }}</div>
                                                    <div class="text-muted small mt-1">Payment receipt waiting for review</div>
                                                </div>
                                            </a>
                                        @endforeach

                                        {{-- List Pending Bookings --}}
                                        @foreach($pendingBookings as $booking)
                                            <a href="{{ route('admin.bookings.show', $booking) }}" class="dropdown-item p-2 rounded d-flex align-items-start gap-2 text-wrap" style="transition: 0.2s; border-bottom: 1px solid rgba(17,24,68,0.05);">
                                                <span class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; flex-shrink: 0;"><i class="bi bi-journal-text"></i></span>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold small text-dark">New Booking</div>
                                                    <div class="font-monospace text-muted" style="font-size: 11px;">#{{ $booking->ref_code }}</div>
                                                    <div class="text-muted small mt-1">New booking requires attention</div>
                                                </div>
                                            </a>
                                        @endforeach

                                        {{-- List New Inquiries --}}
                                        @foreach($newInquiries as $inquiry)
                                            <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="dropdown-item p-2 rounded d-flex align-items-start gap-2 text-wrap" style="transition: 0.2s; border-bottom: 1px solid rgba(17,24,68,0.05);">
                                                <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; flex-shrink: 0;"><i class="bi bi-chat-dots"></i></span>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold small text-dark">New Inquiry</div>
                                                    <div class="text-muted small mt-1">Customer inquiry waiting response</div>
                                                </div>
                                            </a>
                                        @endforeach

                                        @if($notiCount === 0)
                                            <div class="text-center text-muted py-4 small">
                                                <i class="bi bi-check2-circle fs-2 text-success d-block mb-1"></i>
                                                All caught up! No notifications.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Settings Gear Dropdown --}}
                            <div class="dropdown">
                                <button class="btn btn-link p-2 text-white rounded-circle d-flex align-items-center justify-content-center hover-bg-light-white" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 38px; height: 38px; text-decoration: none; background: rgba(255,255,255,0.1);" title="Settings">
                                    <i class="bi bi-gear fs-5"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2" style="border-radius: 12px; min-width: 200px; border: 1px solid rgba(17,24,68,0.08) !important;">
                                    <li>
                                        <a class="dropdown-item rounded py-2 px-3 small d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                                            <i class="bi bi-person-gear"></i> Profile Settings
                                        </a>
                                    </li>
                                    @if(auth()->user()->role === 'admin')
                                        <li>
                                            <a class="dropdown-item rounded py-2 px-3 small d-flex align-items-center gap-2" href="{{ route('admin.users.index') }}">
                                                <i class="bi bi-people"></i> User Management
                                            </a>
                                        </li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" class="m-0">
                                            @csrf
                                            <button type="submit" class="dropdown-item rounded py-2 px-3 small d-flex align-items-center gap-2 text-danger">
                                                <i class="bi bi-box-arrow-right"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>

                        </div>

                        {{-- Date Badge --}}
                        <div class="d-flex align-items-center gap-2 bg-white bg-opacity-10 rounded p-2 px-3" style="backdrop-filter: blur(10px);">
                            <i class="bi bi-calendar3 fs-5 text-white"></i>
                            <span class="small fw-semibold text-white">{{ date('d M Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. TOP SUMMARY STATISTICS GRID --}}
        <div class="row g-3 mb-4">
            {{-- Total Bookings --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm custom-stat-card" style="border-radius: 16px;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="bi bi-journal-text fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase fw-bold small d-block" style="font-size: 11px; letter-spacing: 0.5px;">Total Bookings</span>
                            <h3 class="fw-bold mb-0 text-dark mt-1">{{ $totalBookingsCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Bookings --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm custom-stat-card" style="border-radius: 16px;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase fw-bold small d-block" style="font-size: 11px; letter-spacing: 0.5px;">Pending Review</span>
                            <h3 class="fw-bold mb-0 text-dark mt-1">{{ $pendingBookingsCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Uploaded --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm custom-stat-card" style="border-radius: 16px;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="bi bi-credit-card fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase fw-bold small d-block" style="font-size: 11px; letter-spacing: 0.5px;">To Verify</span>
                            <h3 class="fw-bold mb-0 text-dark mt-1">{{ $paymentVerificationCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paid Bookings --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm custom-stat-card" style="border-radius: 16px;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="bi bi-check2-circle fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase fw-bold small d-block" style="font-size: 11px; letter-spacing: 0.5px;">Paid Bookings</span>
                            <h3 class="fw-bold mb-0 text-dark mt-1">{{ $paidBookingsCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Confirmed Bookings --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm custom-stat-card" style="border-radius: 16px;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon bg-secondary-subtle text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="bi bi-file-earmark-check fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase fw-bold small d-block" style="font-size: 11px; letter-spacing: 0.5px;">Confirmed</span>
                            <h3 class="fw-bold mb-0 text-dark mt-1">{{ $confirmedBookingsCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Cancelled Bookings --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm custom-stat-card" style="border-radius: 16px;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="bi bi-x-circle fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase fw-bold small d-block" style="font-size: 11px; letter-spacing: 0.5px;">Cancelled</span>
                            <h3 class="fw-bold mb-0 text-dark mt-1">{{ $cancelledBookingsCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Active Tours --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm custom-stat-card" style="border-radius: 16px;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="bi bi-map fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase fw-bold small d-block" style="font-size: 11px; letter-spacing: 0.5px;">Active Tours</span>
                            <h3 class="fw-bold mb-0 text-dark mt-1">{{ $activeToursCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- New Inquiries --}}
            <div class="col-xl-3 col-md-6">
                <div class="card h-100 border-0 shadow-sm custom-stat-card" style="border-radius: 16px;">
                    <div class="card-body p-3 d-flex align-items-center gap-3">
                        <div class="stat-icon bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; flex-shrink: 0;">
                            <i class="bi bi-chat-dots fs-4"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase fw-bold small d-block" style="font-size: 11px; letter-spacing: 0.5px;">New Inquiries</span>
                            <h3 class="fw-bold mb-0 text-dark mt-1">{{ $newInquiriesCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. MAIN DASHBOARD CONTENT AREA --}}
        <div class="row g-4">
            
            {{-- LEFT COLUMN: ACTIONS & RECENT BOOKINGS --}}
            <div class="col-lg-8">
                
                {{-- Action Required Section --}}
                @if(count($actionItems) > 0)
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                        <div class="card-header bg-transparent border-0 pt-3 px-3 pb-1">
                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle text-danger"></i> Action Required
                            </h5>
                        </div>
                        <div class="card-body px-3 pb-3">
                            <div class="row g-3">
                                @foreach($actionItems as $item)
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3 d-flex flex-column justify-content-between h-100" style="border-color: rgba(17,24,68,0.08) !important;">
                                            <div class="d-flex align-items-start gap-2 mb-3">
                                                <span class="badge bg-{{ $item['type'] }}-subtle text-{{ $item['type'] }} rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; flex-shrink: 0;">
                                                    <i class="bi {{ $item['icon'] }} fs-6"></i>
                                                </span>
                                                <div>
                                                    <h6 class="fw-bold text-dark mb-1">{{ $item['title'] }}</h6>
                                                    <p class="text-muted small mb-0">{{ $item['description'] }}</p>
                                                </div>
                                            </div>
                                            <a href="{{ $item['link'] }}" class="btn btn-sm btn-outline-{{ $item['type'] }} w-100 rounded-pill mt-auto">
                                                {{ $item['button_text'] }} <i class="bi bi-arrow-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Recent Bookings Table --}}
                <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                    <div class="card-header bg-transparent border-0 pt-3 px-3 pb-1 d-flex align-items-center justify-content-between">
                        <h5 class="fw-bold text-dark mb-0">Recent Bookings</h5>
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-light border rounded-pill px-3">
                            View All <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="card-body px-3 pb-3">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Ref Code</th>
                                        <th>Customer</th>
                                        <th>Tour</th>
                                        <th>Travel Date</th>
                                        <th>Total Price</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentBookings as $booking)
                                        <tr>
                                            <td><span class="font-monospace fw-bold text-dark">#{{ $booking->ref_code }}</span></td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $booking->customer_name }}</div>
                                                <div class="text-muted" style="font-size: 11px;">{{ $booking->email }}</div>
                                            </td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $booking->tour->title ?? '-' }}">
                                                    {{ $booking->tour->title ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="small">{{ \Carbon\Carbon::parse($booking->checkin_date)->format('d M Y') }}</span>
                                            </td>
                                            <td><span class="fw-bold text-dark">${{ number_format($booking->total_price, 2) }}</span></td>
                                            <td>
                                                @if($booking->status === 'pending')
                                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-3">Pending</span>
                                                @elseif($booking->status === 'confirmed')
                                                    <span class="badge bg-info-subtle text-info rounded-pill px-3">Confirmed</span>
                                                @elseif($booking->status === 'payment_uploaded')
                                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">Verification</span>
                                                @elseif($booking->status === 'paid')
                                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">Paid</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Cancelled</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-outline-dark rounded-pill">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No recent bookings found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN: STATUS DISTRIBUTION CHART --}}
            <div class="col-lg-4">
                
                <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                    <div class="card-header bg-transparent border-0 pt-3 px-3 pb-1">
                        <h5 class="fw-bold text-dark mb-0">Booking Status Overview</h5>
                    </div>
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        
                        <div class="mb-4">
                            <p class="text-muted small mb-4">Distribution of booking reservations by status category.</p>
                            
                            {{-- Pending Progress Bar --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-semibold text-dark">Pending Review</span>
                                    <span class="small text-muted">{{ $pendingBookingsCount }} ({{ number_format($statusDistribution['pending'], 1) }}%)</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px; background-color: rgba(17,24,68,0.05);">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $statusDistribution['pending'] }}%; border-radius: 4px;"></div>
                                </div>
                            </div>

                            {{-- Payment Uploaded Progress Bar --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-semibold text-dark">Payment Verification</span>
                                    <span class="small text-muted">{{ $paymentVerificationCount }} ({{ number_format($statusDistribution['payment_uploaded'], 1) }}%)</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px; background-color: rgba(17,24,68,0.05);">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $statusDistribution['payment_uploaded'] }}%; border-radius: 4px;"></div>
                                </div>
                            </div>

                            {{-- Confirmed Progress Bar --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-semibold text-dark">Confirmed</span>
                                    <span class="small text-muted">{{ $confirmedBookingsCount }} ({{ number_format($statusDistribution['confirmed'], 1) }}%)</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px; background-color: rgba(17,24,68,0.05);">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $statusDistribution['confirmed'] }}%; border-radius: 4px;"></div>
                                </div>
                            </div>

                            {{-- Paid Progress Bar --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-semibold text-dark">Paid</span>
                                    <span class="small text-muted">{{ $paidBookingsCount }} ({{ number_format($statusDistribution['paid'], 1) }}%)</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px; background-color: rgba(17,24,68,0.05);">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $statusDistribution['paid'] }}%; border-radius: 4px;"></div>
                                </div>
                            </div>

                            {{-- Cancelled Progress Bar --}}
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-semibold text-dark">Cancelled</span>
                                    <span class="small text-muted">{{ $cancelledBookingsCount }} ({{ number_format($statusDistribution['cancelled'], 1) }}%)</span>
                                </div>
                                <div class="progress" style="height: 8px; border-radius: 4px; background-color: rgba(17,24,68,0.05);">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $statusDistribution['cancelled'] }}%; border-radius: 4px;"></div>
                                </div>
                            </div>

                        </div>

                        {{-- Total Quick Stat Box --}}
                        <div class="p-3 bg-light rounded-3 text-center" style="border: 1px dashed rgba(17,24,68,0.12) !important;">
                            <div class="text-muted small text-uppercase fw-bold mb-1">Total Reservations</div>
                            <h3 class="fw-bold text-dark mb-0">{{ $totalBookingsCount }}</h3>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
