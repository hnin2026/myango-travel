<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification Failed - MyanGo Travel</title>
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
            color: #c0392b;
            line-height: 1.6;
            margin-bottom: 30px;
            font-weight: 600;
        }
        .message-body {
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
        .status-badge {
            display: inline-block;
            background-color: #f8d7da;
            color: #721c24;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
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
        }
        .btn-primary:hover {
            background-color: #232c69;
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
                    We were unable to verify the payment receipt uploaded for booking {{ $booking->ref_code }}.
                </p>
                <p class="message-body">
                    Please ensure that the details match the payment instructions. If you made a bank transfer or mobile banking payment, please check that the transaction reference and receipt clearly show the transfer details. Please use the link below to re-upload a valid payment receipt.
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
                        <div class="summary-label">Booking Status</div>
                        <div class="summary-value">
                            <span class="status-badge">Payment Required</span>
                        </div>
                    </div>
                </div>

                <div class="btn-container">
                    <a href="{{ url('/payment/' . $booking->cancellation_token) }}" class="btn-primary" target="_blank">
                        Upload Payment Receipt
                    </a>
                </div>
            </div>
            
            <div class="footer">
                <p>If you need assistance, please reply to this email or contact our customer support.</p>
                <p style="margin-top: 10px;">&copy; {{ date('Y') }} MyanGo Travel. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
