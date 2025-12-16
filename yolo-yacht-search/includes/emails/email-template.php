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
            max-width: 200px;
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
            display: table;
            width: 100%;
            padding: 14px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            display: table-cell;
            color: #6b7280;
            font-weight: 500;
            width: 40%;
        }
        .detail-value {
            display: table-cell;
            color: #1f2937;
            font-weight: 600;
            text-align: right;
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
        @media only screen and (max-width: 600px) {
            .email-header {
                padding: 40px 20px;
            }
            .email-body {
                padding: 40px 24px;
            }
            .booking-card {
                padding: 24px;
            }
            .highlight-box,
            .success-box {
                padding: 20px;
            }
            .detail-row {
                display: block;
            }
            .detail-label,
            .detail-value {
                display: block;
                width: 100%;
                text-align: left;
            }
            .detail-value {
                margin-top: 6px;
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
