<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Alert - {{ $evaluation_period }}</title>
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
            background: linear-gradient(135deg, #e74c3c, #c0392b);
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
        .icon {
            font-size: 36px;
            margin: 10px 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .alert {
            background: linear-gradient(135deg, #fff5f5, #fed7d7);
            border: 2px solid #e74c3c;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            margin: 20px 0;
        }
        .alert h2 {
            color: #c53030;
            margin-top: 0;
            font-size: 24px;
        }
        .ranking {
            font-size: 36px;
            font-weight: bold;
            color: #e74c3c;
            margin: 10px 0;
        }
        .score {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
        }
        .score h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .score-value {
            font-size: 32px;
            font-weight: bold;
            color: #e74c3c;
        }
        .suggestions {
            background-color: #f0fff4;
            border-left: 4px solid #38a169;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .suggestions h3 {
            color: #2f855a;
            margin-top: 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
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
        .warning {
            color: #f39c12;
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
            <div class="icon">⚠️</div>
            <h1>Performance Alert</h1>
            <p>Action Required - {{ $evaluation_period }}</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $employee_name }}</strong>,</p>

            <p>This is an important performance alert regarding your evaluation results for the period <strong>{{ $evaluation_period }}</strong>.</p>

            <div class="alert">
                <h2>🚨 Performance Alert</h2>
                <div class="ranking">Ranking: {{ $ranking }}</div>
                <p>Your current performance ranking requires immediate attention.</p>
            </div>

            <div class="score">
                <h3>📊 Performance Score</h3>
                <div class="score-value">{{ number_format($total_score * 100, 2) }}%</div>
                <p>Your total weighted score: <strong>{{ $total_score }}</strong></p>
            </div>

            <p>Based on your current performance, we've identified several areas that need improvement:</p>

            <div class="suggestions">
                <h3>💡 Improvement Suggestions</h3>
                <ul>
                    <li>Schedule a meeting with your supervisor for guidance</li>
                    <li>Review previous evaluation feedback for improvement areas</li>
                    <li>Consider additional training in your area of expertise</li>
                    <li>Focus on improving core competencies</li>
                    <li>Set specific, measurable goals for the next period</li>
                </ul>
            </div>

            <p><strong>What This Means:</strong></p>
            <ul>
                <li>Your performance is below the expected standards</li>
                <li>Immediate action is required to improve</li>
                <li>Support and resources are available to help you</li>
                <li>Regular check-ins will be scheduled</li>
            </ul>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/evaluations" class="cta-button">
                    📝 Review Evaluations
                </a>
            </div>

            <p><strong>Next Steps:</strong></p>
            <ol>
                <li><strong>Immediate:</strong> Schedule a meeting with your supervisor</li>
                <li><strong>This Week:</strong> Review your evaluation feedback</li>
                <li><strong>This Month:</strong> Create an improvement plan</li>
                <li><strong>Ongoing:</strong> Regular progress check-ins</li>
            </ol>

            <p><strong>Support Available:</strong></p>
            <ul>
                <li>One-on-one coaching sessions</li>
                <li>Training and development programs</li>
                <li>Mentorship opportunities</li>
                <li>Performance improvement resources</li>
            </ul>
        </div>

        <div class="footer">
            <p>⚠️ This is an automated performance alert from the SAW (Simple Additive Weighting) System.</p>
            <p>Please take immediate action to address these performance concerns.</p>
            <p>Your success is important to us, and we're here to support your improvement.</p>
            <p><small>Generated on {{ now()->format('d M Y H:i') }}</small></p>
        </div>
    </div>
</body>
</html>
