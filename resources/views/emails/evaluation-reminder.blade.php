<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Reminder - {{ $evaluation_period }}</title>
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
        .stats {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .stats h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .progress-bar {
            background-color: #e9ecef;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            background: linear-gradient(90deg, #28a745, #20c997);
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
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
        .success {
            color: #27ae60;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Evaluation Reminder</h1>
            <p><strong>Period: {{ $evaluation_period }}</strong></p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $employee_name }}</strong>,</p>

            <p>This is a friendly reminder that the employee evaluation period <strong>{{ $evaluation_period }}</strong> is currently in progress.</p>

            <div class="stats">
                <h3>📊 Your Evaluation Progress</h3>
                <p><strong>Completed Evaluations:</strong> {{ $completed_evaluations }} / {{ $total_criteria }}</p>

                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $completion_percentage }}%"></div>
                </div>

                <p><strong>Completion Rate:</strong>
                    @if($completion_percentage >= 80)
                        <span class="success">{{ $completion_percentage }}%</span>
                    @elseif($completion_percentage >= 50)
                        <span style="color: #f39c12; font-weight: bold;">{{ $completion_percentage }}%</span>
                    @else
                        <span class="highlight">{{ $completion_percentage }}%</span>
                    @endif
                </p>

                @if($remaining_criteria > 0)
                    <p><strong>Remaining Criteria:</strong> <span class="highlight">{{ $remaining_criteria }}</span></p>
                @else
                    <p><strong>Status:</strong> <span class="success">✅ Complete!</span></p>
                @endif
            </div>

            @if($remaining_criteria > 0)
                <p>Please complete the remaining evaluations to ensure a comprehensive assessment of your performance.</p>

                <p><strong>Next Steps:</strong></p>
                <ul>
                    <li>Log into the SAW system</li>
                    <li>Navigate to the evaluation section</li>
                    <li>Complete the remaining {{ $remaining_criteria }} criteria</li>
                    <li>Submit your evaluations</li>
                </ul>

                <div style="text-align: center;">
                    <a href="{{ config('app.url') }}/evaluations" class="cta-button">
                        📝 Complete Evaluations
                    </a>
                </div>
            @else
                <p>🎉 Congratulations! You have completed all evaluations for this period.</p>
                <p>Your comprehensive evaluation will be processed and results will be available soon.</p>
            @endif
        </div>

        <div class="footer">
            <p>This is an automated reminder from the SAW (Simple Additive Weighting) System.</p>
            <p>If you have any questions, please contact your supervisor or HR department.</p>
            <p><small>Generated on {{ now()->format('d M Y H:i') }}</small></p>
        </div>
    </div>
</body>
</html>
