<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MyanGo Travel')</title>
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
        .message-section {
            margin-top: 30px;
            border-top: 1px solid #eef2f6;
            padding-top: 25px;
        }
        .message-card {
            background-color: #f8fafc;
            border-left: 4px solid #111844;
            padding: 20px;
            border-radius: 4px;
            font-size: 14px;
            color: #333333;
            line-height: 1.6;
            white-space: pre-line;
        }
        .status-badge {
            display: inline-block;
            font-size: 13px;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
        }
        .status-badge.status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-badge.status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-badge.status-pending {
            background-color: #ebdcb9;
            color: #8c6d30;
        }
        .reason-box {
            background-color: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 30px;
            font-size: 14px;
            color: #c53030;
            line-height: 1.6;
        }
        .reason-title {
            font-weight: 700;
            margin-bottom: 6px;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
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
        @yield('styles')
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header" style="@yield('header-style')">
                <h1>MyanGo Travel</h1>
            </div>
            
            <div class="content">
                @yield('content')
            </div>
            
            <div class="footer">
                @section('footer')
                    <p>&copy; {{ date('Y') }} MyanGo Travel. All rights reserved.</p>
                @show
            </div>
        </div>
    </div>
</body>
</html>
