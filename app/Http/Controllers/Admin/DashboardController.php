<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\Inquiry;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        // 1. Summary Counts
        $totalBookingsCount = Booking::count();
        $pendingBookingsCount = Booking::where('status', 'pending')->count();
        $paymentVerificationCount = Booking::where('status', 'payment_uploaded')->count();
        $confirmedBookingsCount = Booking::where('status', 'confirmed')->count();
        $paidBookingsCount = Booking::where('status', 'paid')->count();
        $cancelledBookingsCount = Booking::where('status', 'cancelled')->count();
        $activeToursCount = Tour::where('status', 'active')->count();
        $newInquiriesCount = Inquiry::where('status', 'new')->count();

        // 2. Action Required items list
        $actionItems = [];
        if ($pendingBookingsCount > 0) {
            $actionItems[] = [
                'type' => 'warning',
                'title' => 'Pending Bookings',
                'description' => "{$pendingBookingsCount} booking(s) waiting for confirmation.",
                'link' => route('admin.bookings.index'),
                'icon' => 'bi-journal-text',
                'button_text' => 'Review Bookings'
            ];
        }
        if ($paymentVerificationCount > 0) {
            $actionItems[] = [
                'type' => 'info',
                'title' => 'Payment Verification',
                'description' => "{$paymentVerificationCount} payment receipt(s) waiting for verification.",
                'link' => route('admin.bookings.index'),
                'icon' => 'bi-credit-card-2-front',
                'button_text' => 'Verify Payments'
            ];
        }
        if ($newInquiriesCount > 0) {
            $actionItems[] = [
                'type' => 'primary',
                'title' => 'New Inquiries',
                'description' => "{$newInquiriesCount} new inquiry/inquiries waiting for response.",
                'link' => route('admin.inquiries.index'),
                'icon' => 'bi-chat-dots',
                'button_text' => 'Respond to Inquiries'
            ];
        }

        // 3. Recent Bookings (latest 5)
        $recentBookings = Booking::with(['tour', 'hotel', 'travelPeriod'])
            ->latest()
            ->limit(5)
            ->get();

        // 4. Booking Status Distribution for mini visual summary
        $statusDistribution = [
            'pending' => $totalBookingsCount > 0 ? ($pendingBookingsCount / $totalBookingsCount) * 100 : 0,
            'confirmed' => $totalBookingsCount > 0 ? ($confirmedBookingsCount / $totalBookingsCount) * 100 : 0,
            'payment_uploaded' => $totalBookingsCount > 0 ? ($paymentVerificationCount / $totalBookingsCount) * 100 : 0,
            'paid' => $totalBookingsCount > 0 ? ($paidBookingsCount / $totalBookingsCount) * 100 : 0,
            'cancelled' => $totalBookingsCount > 0 ? ($cancelledBookingsCount / $totalBookingsCount) * 100 : 0,
        ];

        return view('admin.dashboard', compact(
            'totalBookingsCount',
            'pendingBookingsCount',
            'paymentVerificationCount',
            'confirmedBookingsCount',
            'paidBookingsCount',
            'cancelledBookingsCount',
            'activeToursCount',
            'newInquiriesCount',
            'actionItems',
            'recentBookings',
            'statusDistribution'
        ));
    }
}
