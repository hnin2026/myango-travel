<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Required - MyanGo Travel</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f4f6f8;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(17, 24, 68, 0.05);
        }
        .header {
            background-color: #111844;
            padding: 30px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 40px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #111844;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .intro {
            font-size: 15px;
            color: #55688a;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #eef2f6;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
        }
        .summary-title {
            font-size: 16px;
            font-weight: 700;
            color: #111844;
            margin-top: 0;
            margin-bottom: 16px;
            border-bottom: 2px solid #eef2f6;
            padding-bottom: 8px;
        }
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        .summary-row:last-child {
            margin-bottom: 0;
        }
        .summary-label {
            display: table-cell;
            width: 40%;
            font-size: 14px;
            color: #7288ae;
            font-weight: 500;
        }
        .summary-value {
            display: table-cell;
            width: 60%;
            font-size: 14px;
            color: #111844;
            font-weight: 700;
            text-align: right;
        }
        .btn-container {
            text-align: center;
            margin: 35px 0;
        }
        .btn-primary {
            display: inline-block;
            background-color: #111844;
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 36px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(17, 24, 68, 0.15);
            transition: background-color 0.2s ease;
            margin: 5px;
        }
        .btn-primary:hover {
            background-color: #232c69;
        }
        .btn-secondary {
            display: inline-block;
            background-color: #c0392b;
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 36px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(192, 57, 43, 0.15);
            transition: background-color 0.2s ease;
            margin: 5px;
        }
        .btn-secondary:hover {
            background-color: #a93226;
        }
        .methods-section {
            margin-top: 40px;
            border-top: 1px solid #eef2f6;
            padding-top: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #111844;
            margin-top: 0;
            margin-bottom: 20px;
        }
        .method-card {
            background-color: #fcfbf9;
            border: 1px solid #ebdcb9;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .method-card:last-child {
            margin-bottom: 0;
        }
        .method-header {
            font-size: 15px;
            font-weight: 700;
            color: #8c6d30;
            margin-top: 0;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .method-details {
            font-size: 14px;
            color: #55688a;
            line-height: 1.6;
        }
        .method-details strong {
            color: #111844;
        }
        .footer {
            background-color: #f8fafc;
            padding: 30px 40px;
            text-align: center;
            border-top: 1px solid #eef2f6;
        }
        .footer p {
            margin: 0;
            font-size: 13px;
            color: #7288ae;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>MyanGo Travel</h1>
            </div>
            
            <div class="content">
                <p class="greeting">Dear {{ $booking->customer_name }},</p>
                <p class="intro">
                    Your booking request has been reviewed and is now <strong>Confirmed</strong>! To secure your seats, please complete your payment and upload the transaction receipt using the link below before the payment deadline.
                </p>
                
                <div class="summary-card">
                    <div class="summary-title">Booking Details</div>
                    <div class="summary-row">
                        <div class="summary-label">Booking Reference</div>
                        <div class="summary-value">{{ $booking->ref_code }}</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Tour Title</div>
                        <div class="summary-value">{{ $booking->tour?->title }}</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Travel Dates</div>
                        <div class="summary-value">{{ $booking->checkin_date }} &rarr; {{ $booking->checkout_date }}</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Total Amount</div>
                        <div class="summary-value">USD {{ number_format($booking->total_price, 2) }}</div>
                    </div>
                    <div class="summary-row">
                        <div class="summary-label">Payment Deadline</div>
                        <div class="summary-value">
                            @if($booking->payment_deadline instanceof \Carbon\Carbon || $booking->payment_deadline instanceof \DateTime)
                                {{ $booking->payment_deadline->format('F d, Y') }}
                            @else
                                {{ \Carbon\Carbon::parse($booking->payment_deadline)->format('F d, Y') }}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="btn-container">
                    <a href="{{ url('/payment/' . $booking->cancellation_token) }}" class="btn-primary" target="_blank">
                        Upload Payment Receipt
                    </a>
                    <a href="{{ url('/booking/cancel/' . $booking->cancellation_token) }}" class="btn-secondary" target="_blank">
                        Cancel Booking
                    </a>
                </div>

                <div class="methods-section">
                    <div class="section-title">Payment Methods</div>
                    
                    <!-- International Customers -->
                    <div class="method-card">
                        <div class="method-header">International Customers</div>
                        <div class="method-details">
                            <strong>Bank Transfer (USD)</strong><br>
                            Bank Name: <strong>AYA Bank</strong><br>
                            Account Name: <strong>MyanGo Travel</strong><br>
                            Account Number: <strong>123456789</strong><br>
                            SWIFT Code: <strong>AYABMMMY</strong><br>
                            Reference: <strong>{{ $booking->ref_code }}</strong>
                        </div>
                    </div>
                    
                    <!-- Myanmar Customers -->
                    <div class="method-card">
                        <div class="method-header">Myanmar Customers</div>
                        <div class="method-details">
                            <strong>KBZPay</strong> | <strong>WavePay</strong> | <strong>AYA Mobile Banking</strong><br>
                            Reference: <strong>{{ $booking->ref_code }}</strong><br>
                            Amount: <strong>USD {{ number_format($booking->total_price, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer">
                <p>If you have any questions or require assistance, please contact our customer support team.</p>
                <p style="margin-top: 10px;">&copy; {{ date('Y') }} MyanGo Travel. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
