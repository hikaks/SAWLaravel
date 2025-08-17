<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Congratulations! Top Performer - {{ $evaluation_period }}</title>
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
            border-bottom: 3px solid #f39c12;
            padding-bottom: 20px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 30px -30px;
            padding: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .trophy {
            font-size: 48px;
            margin: 10px 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .achievement {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            border: 2px solid #f39c12;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            margin: 20px 0;
        }
        .achievement h2 {
            color: #d68910;
            margin-top: 0;
            font-size: 24px;
        }
        .ranking {
            font-size: 36px;
            font-weight: bold;
            color: #e67e22;
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
            color: #27ae60;
        }
        .category {
            display: inline-block;
            background-color: #27ae60;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            margin: 10px 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
            transition: transform 0.2s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
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
            color: #e67e22;
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
            <div class="trophy">🏆</div>
            <h1>Congratulations!</h1>
            <p>You're a Top Performer!</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $employee_name }}</strong>,</p>

            <p>We are delighted to inform you that you have achieved <strong>outstanding performance</strong> during the evaluation period <strong>{{ $evaluation_period }}</strong>!</p>

            <div class="achievement">
                <h2>🎯 Your Achievement</h2>
                <div class="ranking">{{ $ranking_text }}</div>
                <p>Out of all employees evaluated, you ranked <strong>{{ $ranking }}</strong>!</p>
                <div class="category">{{ $ranking_category }}</div>
            </div>

            <div class="score">
                <h3>📊 Performance Score</h3>
                <div class="score-value">{{ number_format($total_score * 100, 2) }}%</div>
                <p>Your total weighted score: <strong>{{ $total_score }}</strong></p>
            </div>

            <p>This exceptional performance demonstrates your:</p>
            <ul>
                <li><strong>Dedication</strong> to excellence in your role</li>
                <li><strong>Commitment</strong> to continuous improvement</li>
                <li><strong>Leadership</strong> and positive influence on your team</li>
                <li><strong>Professional growth</strong> and development</li>
            </ul>

            <p>Your hard work and achievements are truly commendable and set a great example for your colleagues.</p>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/results" class="cta-button">
                    🏆 View Full Results
                </a>
            </div>

            <p><strong>What's Next?</strong></p>
            <ul>
                <li>Your performance will be reviewed by management</li>
                <li>Consider sharing your best practices with your team</li>
                <li>Continue setting high standards for excellence</li>
                <li>Look for opportunities to mentor others</li>
            </ul>
        </div>

        <div class="footer">
            <p>🎉 Congratulations once again on this outstanding achievement!</p>
            <p>This recognition is a testament to your hard work and dedication.</p>
            <p>Keep up the excellent work!</p>
            <p><small>Generated on {{ now()->format('d M Y H:i') }}</small></p>
        </div>
    </div>
</body>
</html>
