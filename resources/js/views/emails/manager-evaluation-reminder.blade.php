<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Evaluation Reminder - {{ $evaluation_period }}</title>
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
            border-bottom: 3px solid #e67e22;
            padding-bottom: 20px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #e67e22, #d35400);
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
        .manager-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #e67e22;
        }
        .manager-info h3 {
            color: #d35400;
            margin-top: 0;
        }
        .stats {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .stats h3 {
            color: #856404;
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
            background: linear-gradient(90deg, #e67e22, #d35400);
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #e67e22, #d35400);
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(230, 126, 34, 0.3);
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
        .warning {
            color: #f39c12;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">👨‍💼</div>
            <h1>Manager Evaluation Reminder</h1>
            <p>Action Required - {{ $evaluation_period }}</p>
        </div>

        <div class="content">
            <p>Dear <strong>{{ $manager_name }}</strong>,</p>

            <p>This is a reminder that you have <strong>managerial responsibilities</strong> for the evaluation period <strong>{{ $evaluation_period }}</strong>.</p>

            <div class="manager-info">
                <h3>👨‍💼 Manager Information</h3>
                <p><strong>Name:</strong> {{ $manager_name }}</p>
                <p><strong>Role:</strong> Manager</p>
                <p><strong>Department:</strong> {{ $department ?? 'All Departments' }}</p>
                <p><strong>Date:</strong> {{ now()->format('d M Y H:i') }}</p>
            </div>

            <div class="stats">
                <h3>📊 Team Evaluation Progress</h3>
                <p><strong>Team Members:</strong> {{ $team_count ?? 0 }} employees</p>
                <p><strong>Completed Evaluations:</strong> {{ $completed_evaluations ?? 0 }} / {{ $total_evaluations ?? 0 }}</p>

                @if(isset($completion_percentage))
                <div class="progress-bar">
                    <div class="progress-fill" style="width: {{ $completion_percentage }}%"></div>
                </div>

                <p><strong>Completion Rate:</strong>
                    @if($completion_percentage >= 80)
                        <span class="success">{{ $completion_percentage }}%</span>
                    @elseif($completion_percentage >= 60)
                        <span class="warning">{{ $completion_percentage }}%</span>
                    @else
                        <span class="highlight">{{ $completion_percentage }}%</span>
                    @endif
                </p>
                @endif

                <p><strong>Status:</strong>
                    @if(($completion_percentage ?? 0) >= 80)
                        <span class="success">✅ On Track</span>
                    @elseif(($completion_percentage ?? 0) >= 60)
                        <span class="warning">⚠️ Needs Attention</span>
                    @else
                        <span class="highlight">🚨 Critical</span>
                    @endif
                </p>
            </div>

            <p><strong>Your Managerial Responsibilities:</strong></p>
            <ul>
                <li>Review and approve team member evaluations</li>
                <li>Ensure evaluation quality and consistency</li>
                <li>Provide feedback and guidance to team members</li>
                <li>Monitor evaluation progress and deadlines</li>
                <li>Address any evaluation-related issues</li>
                <li>Support team development and improvement</li>
            </ul>

            @if(($completion_percentage ?? 0) < 80)
                <p><strong>⚠️ Immediate Actions Required:</strong></p>
                <ul>
                    <li>Follow up with team members who haven't completed evaluations</li>
                    <li>Schedule evaluation review meetings</li>
                    <li>Provide additional support and resources</li>
                    <li>Set clear deadlines and expectations</li>
                </ul>
            @endif

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/evaluations" class="cta-button">
                    📝 Review Evaluations
                </a>
            </div>

            <p><strong>Next Steps:</strong></p>
            <ol>
                <li><strong>Today:</strong> Review current evaluation status</li>
                <li><strong>This Week:</strong> Follow up with incomplete evaluations</li>
                <li><strong>Ongoing:</strong> Provide support and guidance</li>
                <li><strong>Deadline:</strong> Ensure all evaluations are completed</li>
            </ol>

            <p><strong>Support Available:</strong></p>
            <ul>
                <li>HR department assistance</li>
                <li>Evaluation guidelines and templates</li>
                <li>Training and development resources</li>
                <li>Performance management tools</li>
            </ul>
        </div>

        <div class="footer">
            <p>👨‍💼 This is an automated manager reminder from the SAW (Simple Additive Weighting) System.</p>
            <p>As a manager, your leadership is crucial for successful team evaluations.</p>
            <p>Please ensure all team evaluations are completed on time and with quality.</p>
            <p><small>Generated on {{ now()->format('d M Y H:i') }}</small></p>
        </div>
    </div>
</body>
</html>
