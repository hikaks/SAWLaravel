<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Announcement</title>
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
        }
        .header h1 {
            color: #2c3e50;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .announcement {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3498db;
        }
        .announcement h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .cta-button {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>System Announcement</h1>
            <p>Important Update</p>
        </div>

        <div class="content">
            <p>Dear <strong>User</strong>,</p>

            <div class="announcement">
                <h3>📢 Announcement Details</h3>
                <p>This is an important system announcement. Please read carefully.</p>
            </div>

            <p><strong>What This Means for You:</strong></p>
            <ul>
                <li>Please review the information carefully</li>
                <li>Take any necessary actions as indicated</li>
                <li>Contact support if you have questions</li>
                <li>Stay updated with future announcements</li>
            </ul>

            <div style="text-align: center;">
                <a href="#" class="cta-button">
                    🏠 Go to Dashboard
                </a>
            </div>

            <p><strong>Need Help?</strong></p>
            <p>If you have any questions or need assistance, please don't hesitate to contact:</p>
            <ul>
                <li><strong>IT Support:</strong> support@company.com</li>
                <li><strong>HR Department:</strong> hr@company.com</li>
            </ul>
        </div>

        <div class="footer">
            <p>This is an automated system announcement from the SAW (Simple Additive Weighting) System.</p>
            <p>Please ensure you read and understand this information.</p>
            <p><small>Generated on {{ now()->format('d M Y H:i') }}</small></p>
        </div>
    </div>
</body>
</html>
