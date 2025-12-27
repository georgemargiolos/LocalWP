<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($email_title); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: #f3f4f6;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .email-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            padding: 50px 30px;
            text-align: center;
        }
        .email-header img {
            max-width: 180px;
            height: auto;
        }
        .email-header h1 {
            color: #ffffff;
            margin: 25px 0 0 0;
            font-size: 28px;
            font-weight: 700;
        }
        .email-body {
            padding: 50px 40px;
        }
        .email-body h2 {
            color: #1f2937;
            font-size: 22px;
            margin: 0 0 24px 0;
        }
        .email-body p {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.7;
            margin: 0 0 20px 0;
        }
        .booking-card {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 32px;
            margin: 32px 0;
        }
        .booking-card h3 {
            color: #1f2937;
            font-size: 18px;
            margin: 0 0 20px 0;
            padding-bottom: 16px;
            border-bottom: 2px solid #e5e7eb;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 14px 0;
            border-bottom: 1px solid #e5e7eb;
            flex-wrap: wrap;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #6b7280;
            font-weight: 500;
            flex: 0 0 40%;
        }
        .detail-value {
            color: #1f2937;
            font-weight: 600;
            text-align: right;
            flex: 0 0 60%;
        }
        .highlight-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 24px;
            margin: 32px 0;
            border-radius: 6px;
        }
        .highlight-box p {
            margin: 0;
            color: #92400e;
        }
        .success-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 24px;
            margin: 32px 0;
            border-radius: 6px;
        }
        .success-box p {
            margin: 0;
            color: #065f46;
        }
        .info-box {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 24px;
            margin: 32px 0;
            border-radius: 6px;
        }
        .info-box p {
            margin: 0 0 12px 0;
            color: #1e40af;
        }
        .info-box p:last-child {
            margin-bottom: 0;
        }
        .info-box code {
            background: #bfdbfe;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 16px 40px;
            background: #dc2626;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            margin: 24px 0;
        }
        .button:hover {
            background: #b91c1c;
        }
        .button-secondary {
            display: inline-block;
            padding: 14px 32px;
            background: #3b82f6;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 15px;
            margin: 16px 0;
        }
        .button-secondary:hover {
            background: #2563eb;
        }
        .email-footer {
            background: #1f2937;
            padding: 40px 30px;
            text-align: center;
        }
        .email-footer p {
            color: #9ca3af;
            font-size: 14px;
            margin: 10px 0;
        }
        .email-footer a {
            color: #60a5fa;
            text-decoration: none;
        }
        
        /* Mobile Responsive Styles */
        @media only screen and (max-width: 600px) {
            .email-header {
                padding: 40px 20px;
            }
            .email-header img {
                max-width: 150px;
            }
            .email-header h1 {
                font-size: 24px;
            }
            .email-body {
                padding: 32px 20px;
            }
            .email-body h2 {
                font-size: 20px;
            }
            .email-body p {
                font-size: 15px;
            }
            .booking-card {
                padding: 20px;
                margin: 24px 0;
            }
            .booking-card h3 {
                font-size: 16px;
            }
            .detail-row {
                flex-direction: column;
                padding: 12px 0;
            }
            .detail-label {
                flex: 0 0 100%;
                margin-bottom: 4px;
                font-size: 14px;
            }
            .detail-value {
                flex: 0 0 100%;
                text-align: left;
                font-size: 15px;
            }
            .highlight-box,
            .success-box,
            .info-box {
                padding: 20px 16px;
                margin: 24px 0;
            }
            .highlight-box p,
            .success-box p,
            .info-box p {
                font-size: 14px;
            }
            .button {
                display: block;
                text-align: center;
                padding: 16px 24px;
                font-size: 15px;
            }
            .button-secondary {
                display: block;
                text-align: center;
                padding: 14px 20px;
                font-size: 14px;
            }
            .email-footer {
                padding: 32px 20px;
            }
            .email-footer p {
                font-size: 13px;
            }
        }
        
        @media only screen and (max-width: 400px) {
            .email-header {
                padding: 32px 16px;
            }
            .email-header img {
                max-width: 130px;
            }
            .email-body {
                padding: 24px 16px;
            }
            .booking-card {
                padding: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="email-header">
            <?php if (!empty($logo_url)): ?>
            <img src="<?php echo esc_url($logo_url); ?>" alt="YOLO Charters">
            <?php endif; ?>
            <h1><?php echo esc_html($email_heading); ?></h1>
        </div>
        
        <!-- Body -->
        <div class="email-body">
            <?php echo $email_content; ?>
        </div>
        
        <!-- Footer -->
        <div class="email-footer">
            <p><strong>YOLO Charters</strong></p>
            <p>Email: <a href="mailto:info@yolo-charters.com">info@yolo-charters.com</a></p>
            <p>Phone: +30 698 506 4875</p>
            <p>&copy; <?php echo date('Y'); ?> YOLO Charters. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
