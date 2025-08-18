<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo new \Illuminate\Support\EncodedHtmlString($subject ?? 'Custom Notification'); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #3498db;
            padding-bottom: 20px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 30px -30px;
            padding: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .content {
            margin-bottom: 30px;
        }
        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            transition: all 0.3s ease;
        }
        .action-button:hover {
            background: linear-gradient(135deg, #2980b9, #1f5f8b);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .verification-info {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .verification-info h3 {
            color: #1976d2;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 <?php echo new \Illuminate\Support\EncodedHtmlString($subject ?? 'SAW System Notification'); ?></h1>
        </div>

        <div class="content">
            <p>Dear <strong><?php echo new \Illuminate\Support\EncodedHtmlString($recipient_name ?? 'User'); ?></strong>,</p>

            <?php if(isset($message)): ?>
                <div class="verification-info">
                    <h3>📧 <?php echo new \Illuminate\Support\EncodedHtmlString($subject ?? 'Email Verification Required'); ?></h3>
                    <p><?php echo new \Illuminate\Support\EncodedHtmlString($message); ?></p>
                </div>
            <?php endif; ?>

            <?php if(isset($action_url) && isset($action_text)): ?>
                <div style="text-align: center;">
                    <a href="<?php echo new \Illuminate\Support\EncodedHtmlString($action_url); ?>" class="action-button">
                        <?php echo new \Illuminate\Support\EncodedHtmlString($action_text); ?>

                    </a>
                </div>

                <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <p style="margin: 0; font-size: 14px;">
                        <strong>⚠️ Important:</strong> This verification link will expire in 60 minutes for security reasons.
                        If you cannot click the button above, copy and paste this URL into your browser:
                    </p>
                    <p style="margin: 10px 0 0 0; word-break: break-all; font-family: monospace; font-size: 12px; color: #856404;">
                        <?php echo new \Illuminate\Support\EncodedHtmlString($action_url); ?>

                    </p>
                </div>
            <?php endif; ?>

            <p>This is an automated notification from the SAW (Simple Additive Weighting) Employee Evaluation System.</p>

            <p>If you have any questions or need assistance, please contact your system administrator.</p>
        </div>

        <div class="footer">
            <p><strong><?php echo new \Illuminate\Support\EncodedHtmlString(config('app.name', 'SAW Employee Evaluation System')); ?></strong></p>
            <p><small>Generated on <?php echo new \Illuminate\Support\EncodedHtmlString(now()->format('d M Y H:i')); ?></small></p>
        </div>
    </div>
</body>
</html>
<?php /**PATH E:\Pemograman\Laravel\SAWLaravel\resources\views/emails/custom-notification.blade.php ENDPATH**/ ?>