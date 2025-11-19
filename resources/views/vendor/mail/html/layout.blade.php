<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <style>
        /* Master Styles */
        body {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-text-size-adjust: none;
            background-color: #e5edf7;
            color: #333333;
            height: 100%;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            width: 100% !important;
        }

        p, ul, ol, blockquote {
            line-height: 1.6;
            text-align: left;
        }

        a {
            color: #003366;
            text-decoration: none;
        }

        a img {
            border: none;
        }

        /* Typography */
        h1 {
            color: #003366;
            font-size: 24px;
            font-weight: bold;
            margin-top: 0;
            text-align: left;
        }

        h2 {
            color: #003366;
            font-size: 20px;
            font-weight: bold;
            margin-top: 0;
            text-align: left;
        }

        h3 {
            color: #003366;
            font-size: 18px;
            font-weight: bold;
            margin-top: 0;
            text-align: left;
        }

        p {
            color: #555555;
            font-size: 16px;
            line-height: 1.6;
            margin-top: 0;
            text-align: left;
        }

        p.sub {
            font-size: 14px;
        }

        img {
            max-width: 100%;
        }

        /* Layout */
        .wrapper {
            background-color: #e5edf7;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .content {
            margin: 0;
            padding: 0;
            width: 100%;
        }

        /* Header */
        .header {
            padding: 30px 0;
            text-align: center;
            background: linear-gradient(135deg, #003366 0%, #004d99 100%);
        }

        .header a {
            color: #FFCC00;
            font-size: 20px;
            font-weight: bold;
            text-decoration: none;
        }

        .header-logo {
            max-width: 200px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* Body */
        .body {
            background-color: #e5edf7;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        .inner-body {
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 51, 102, 0.1);
            margin: 30px auto;
            padding: 0;
            width: 570px;
        }

        /* Subcopy */
        .subcopy {
            border-top: 1px solid #e8e5ef;
            margin-top: 25px;
            padding-top: 25px;
        }

        .subcopy p {
            font-size: 14px;
            color: #888888;
        }

        /* Footer */
        .footer {
            margin: 0 auto;
            padding: 32px 0;
            text-align: center;
            width: 570px;
        }

        .footer p {
            color: #003366;
            font-size: 13px;
            text-align: center;
        }

        .footer a {
            color: #003366;
            text-decoration: underline;
        }

        /* Tables */
        .table table {
            margin: 30px auto;
            width: 100%;
        }

        .table th {
            border-bottom: 1px solid #e8e5ef;
            margin: 0;
            padding-bottom: 8px;
        }

        .table td {
            color: #555555;
            font-size: 15px;
            line-height: 18px;
            margin: 0;
            padding: 10px 0;
        }

        .content-cell {
            max-width: 100vw;
            padding: 40px 35px;
        }

        /* Buttons */
        .action {
            margin: 30px auto;
            padding: 0;
            text-align: center;
            width: 100%;
        }

        .button {
            -webkit-text-size-adjust: none;
            border-radius: 8px;
            color: #ffffff;
            display: inline-block;
            overflow: hidden;
            text-decoration: none;
        }

        .button-blue, .button-primary {
            background-color: #003366;
            border-bottom: 6px solid #002244;
            border-left: 12px solid #003366;
            border-right: 12px solid #003366;
            border-top: 6px solid #003366;
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
            font-weight: bold;
        }

        .button-success {
            background-color: #10b981;
            border-bottom: 6px solid #059669;
            border-left: 12px solid #10b981;
            border-right: 12px solid #10b981;
            border-top: 6px solid #10b981;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .button-error {
            background-color: #ef4444;
            border-bottom: 6px solid #dc2626;
            border-left: 12px solid #ef4444;
            border-right: 12px solid #ef4444;
            border-top: 6px solid #ef4444;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* Panels */
        .panel {
            border-left: 3px solid #003366;
            margin: 21px 0;
        }

        .panel-content {
            background-color: #f8fafc;
            color: #555555;
            padding: 16px;
            border-radius: 6px;
        }

        .panel-content p {
            color: #555555;
        }

        .panel-item {
            padding: 0;
        }

        .panel-item p:last-of-type {
            margin-bottom: 0;
            padding-bottom: 0;
        }

        /* Utilities */
        .break-all {
            word-break: break-all;
        }

        /* Custom UNAH Styles */
        .unah-header-bar {
            background: linear-gradient(135deg, #003366 0%, #004d99 100%);
            padding: 25px;
            text-align: center;
        }

        .unah-logo-container {
            text-align: center;
            padding: 20px 0;
            background-color: #ffffff;
        }

        .unah-footer-text {
            color: #003366;
            font-size: 13px;
            font-weight: 600;
            margin-top: 10px;
        }

        .unah-divider {
            border: 0;
            height: 2px;
            background: linear-gradient(to right, transparent, #FFCC00, transparent);
            margin: 25px 0;
        }

        .unah-warning-text {
            background-color: #fff7ed;
            border-left: 4px solid #FFCC00;
            padding: 12px 16px;
            margin: 20px 0;
            border-radius: 6px;
            font-size: 14px;
            color: #666666;
        }

        .unah-greeting {
            color: #003366;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .inner-body {
                width: 100% !important;
                margin: 15px auto !important;
                border-radius: 12px !important;
            }

            .footer {
                width: 100% !important;
            }

            .content-cell {
                padding: 30px 20px !important;
            }

            .header-logo {
                max-width: 160px !important;
            }

            h1 {
                font-size: 20px !important;
            }

            h2 {
                font-size: 18px !important;
            }

            h3 {
                font-size: 16px !important;
            }

            p {
                font-size: 15px !important;
            }
        }
    </style>
</head>
<body>
    <table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Header with UNAH Logo -->
                    <tr>
                        <td class="unah-header-bar">
                            <table align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="unah-logo-container">
                                        <a href="{{ config('app.url') }}">
                                            <img src="{{ asset('img/UNAH-version-horizontal.png') }}" class="header-logo" alt="UNAH Logo">
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{ $header ?? '' }}

                    <!-- Email Body -->
                    <tr>
                        <td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
                            <table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <!-- Body content -->
                                <tr>
                                    <td class="content-cell">
                                        {{ Illuminate\Mail\Markdown::parse($slot) }}

                                        {{ $subcopy ?? '' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td>
                            <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
                                <tr>
                                    <td class="content-cell" align="center">
                                        <hr class="unah-divider">
                                        <p class="unah-footer-text">© {{ date('Y') }} Práctica Profesional Supervisada - UNAH</p>
                                        <p style="color: #888888; font-size: 12px; margin-top: 10px;">
                                            Universidad Nacional Autónoma de Honduras<br>
                                            Ciudad Universitaria, Tegucigalpa, Honduras
                                        </p>
                                        {{ $footer ?? '' }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
