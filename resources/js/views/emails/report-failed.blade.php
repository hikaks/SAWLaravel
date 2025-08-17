<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Generation Failed</title>
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
            border-bottom: 3px solid #e74c3c;
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
        .error-details {
            background-color: #fdf2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .error-details h3 {
            color: #dc2626;
            margin-top: 0;
        }
        .error-message {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 15px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #dc2626;
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
        .highlight {
            color: #e74c3c;
            font-weight: bold;
        }
        .info {
            color: #3498db;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Report Generation Failed</h1>
            <p><strong>Report Type: {{ $report_type ?? 'Unknown' }}</strong></p>
            @if(isset($evaluation_period))
                <p><strong>Period: {{ $evaluation_period }}</strong></p>
            @endif
        </div>

        <div class="content">
            <p>Dear <strong>{{ $user_name ?? 'User' }}</strong>,</p>

            <p>We regret to inform you that the report generation job has failed. The system encountered an error while processing your request.</p>

            <div class="error-details">
                <h3>📋 Error Details</h3>
                <p><strong>Report Type:</strong> <span class="info">{{ $report_type ?? 'Unknown' }}</span></p>
                @if(isset($evaluation_period))
                    <p><strong>Evaluation Period:</strong> <span class="info">{{ $evaluation_period }}</span></p>
                @endif
                <p><strong>Failed At:</strong> <span class="highlight">{{ $failed_at ?? now()->format('Y-m-d H:i:s') }}</span></p>
                <p><strong>Job ID:</strong> <span class="info">{{ $job_id ?? 'Unknown' }}</span></p>
            </div>

            @if(isset($error_message))
                <div class="error-message">
                    <strong>Error Message:</strong><br>
                    {{ $error_message }}
                </div>
            @endif

            <p><strong>What happened?</strong></p>
            <ul>
                <li>The system attempted to generate your requested report</li>
                <li>An error occurred during the generation process</li>
                <li>The job was marked as failed and logged for investigation</li>
            </ul>

            <p><strong>What you can do:</strong></p>
            <ul>
                <li>Try generating the report again</li>
                <li>Check if the required data is available</li>
                <li>Contact system administrator if the issue persists</li>
            </ul>

            <p><strong>Next Steps:</strong></p>
            <p>The system administrators have been notified of this failure and will investigate the issue. You may try again in a few minutes, or contact support if the problem continues.</p>
        </div>

        <div class="footer">
            <p>This is an automated notification from the SAW (Simple Additive Weighting) System.</p>
            <p>If you have any questions, please contact your system administrator or IT support team.</p>
            <p><small>Generated on {{ now()->format('d M Y H:i') }}</small></p>
        </div>
    </div>
</body>
</html>
