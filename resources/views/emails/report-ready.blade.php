<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Ready for Download</title>
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
            border-bottom: 3px solid #27ae60;
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
        .report-details {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .report-details h3 {
            color: #0369a1;
            margin-top: 0;
        }
        .download-section {
            text-align: center;
            margin: 30px 0;
            padding: 25px;
            background-color: #f8fafc;
            border-radius: 8px;
        }
        .cta-button {
            display: inline-block;
            background-color: #27ae60;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            background-color: #229954;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
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
            color: #27ae60;
            font-weight: bold;
        }
        .info {
            color: #3498db;
            font-weight: bold;
        }
        .warning {
            color: #f39c12;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Report Ready for Download</h1>
            <p><strong>Report Type: {{ $report_type ?? 'Unknown' }}</strong></p>
            @if(isset($evaluation_period))
                <p><strong>Period: {{ $evaluation_period }}</strong></p>
            @endif
        </div>

        <div class="content">
            <p>Dear <strong>{{ $user_name ?? 'User' }}</strong>,</p>

            <p>Great news! Your requested report has been successfully generated and is ready for download. The system has processed all the data and created a comprehensive report based on your specifications.</p>

            <div class="report-details">
                <h3>📋 Report Information</h3>
                <p><strong>Report Type:</strong> <span class="info">{{ $report_type ?? 'Unknown' }}</span></p>
                @if(isset($evaluation_period))
                    <p><strong>Evaluation Period:</strong> <span class="info">{{ $evaluation_period }}</span></p>
                @endif
                <p><strong>Generated At:</strong> <span class="highlight">{{ $generated_at ?? now()->format('Y-m-d H:i:s') }}</span></p>
                <p><strong>File Format:</strong> <span class="info">{{ $file_format ?? 'CSV' }}</span></p>
                <p><strong>File Size:</strong> <span class="info">{{ $file_size ?? 'Unknown' }}</span></p>
            </div>

            <div class="download-section">
                <h3>⬇️ Download Your Report</h3>
                <p>Click the button below to download your report. The download link is secure and will expire after use for security purposes.</p>

                <a href="{{ $download_url ?? '#' }}" class="cta-button">
                    📥 Download Report
                </a>

                <p><small class="warning">⚠️ This link will expire after use for security reasons.</small></p>
            </div>

            <p><strong>What's included in your report:</strong></p>
            <ul>
                <li>Comprehensive data analysis and calculations</li>
                <li>Formatted and organized information</li>
                <li>Ready for import into spreadsheet applications</li>
                <li>Professional presentation suitable for stakeholders</li>
            </ul>

            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Download the report using the button above</li>
                <li>Review the data for accuracy and completeness</li>
                <li>Share with relevant team members as needed</li>
                <li>Store in your organization's document management system</li>
            </ul>

            <p><strong>Need Help?</strong></p>
            <p>If you have any questions about the report content or encounter any issues with the download, please contact your system administrator or IT support team.</p>
        </div>

        <div class="footer">
            <p>This is an automated notification from the SAW (Simple Additive Weighting) System.</p>
            <p>If you have any questions, please contact your system administrator or IT support team.</p>
            <p><small>Generated on {{ now()->format('d M Y H:i') }}</small></p>
        </div>
    </div>
</body>
</html>
