<?php
session_start();

// Check if study plan exists in session
$studyPlan = null;
$userId = null;
$hasError = false;
$errorMessage = '';

if (!isset($_SESSION['study_plan']) || !isset($_SESSION['user_id'])) {
    $hasError = true;
    $errorMessage = 'No Study Plan Found';
} else {
    $studyPlan = $_SESSION['study_plan'];
    $userId = $_SESSION['user_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Personalized Study Plan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .study-plan {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .plan-header {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .plan-header h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .plan-meta {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .meta-item {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 20px;
            text-align: center;
        }

        .meta-item strong {
            display: block;
            font-size: 1.2rem;
        }

        .plan-content {
            padding: 30px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h3 {
            color: #4CAF50;
            font-size: 1.5rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            width: 24px;
            height: 24px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .profile-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #4CAF50;
        }

        .profile-card h4 {
            color: #333;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .profile-card p {
            color: #666;
            font-size: 1.1rem;
        }

        .recommendations-list, .focus-areas-list {
            list-style: none;
        }

        .recommendations-list li, .focus-areas-list li {
            background: #e8f5e8;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #4CAF50;
            transition: transform 0.2s;
        }

        .recommendations-list li:hover, .focus-areas-list li:hover {
            transform: translateX(5px);
        }

        .recommendations-list li::before {
            content: "💡";
            margin-right: 10px;
        }

        .focus-areas-list li::before {
            content: "🎯";
            margin-right: 10px;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }

        .no-plan {
            text-align: center;
            padding: 50px;
            color: #666;
        }

        .no-plan h3 {
            margin-bottom: 20px;
            color: #333;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .plan-meta {
                gap: 15px;
            }

            .meta-item {
                padding: 8px 15px;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Your AI-Generated Study Plan</h1>
            <p>Personalized recommendations based on your learning profile</p>
        </div>

        <?php if ($hasError): ?>
            <div class="study-plan">
                <div class="no-plan">
                    <h3><?php echo htmlspecialchars($errorMessage); ?></h3>
                    <p>Please complete the questionnaire first to generate your personalized study plan.</p>
                    <a href="questionnaire.php" class="btn btn-primary">Take Questionnaire</a>
                </div>
            </div>
        <?php else:
            // Display any errors
            if (isset($studyPlan['error']) && $studyPlan['error']) {
                echo '<div class="error-message">
                        <strong>Note:</strong> There was an issue with the ML system. 
                        Showing a basic study plan based on your responses.
                      </div>';
            }
        ?>

        <div class="study-plan">
            <div class="plan-header">
                <h2>Study Plan for Student #<?php echo htmlspecialchars($userId); ?></h2>
                <div class="plan-meta">
                    <div class="meta-item">
                        <strong><?php echo isset($studyPlan['duration_days']) ? $studyPlan['duration_days'] : '21'; ?></strong>
                        <span>Day Plan</span>
                    </div>
                    <div class="meta-item">
                        <strong><?php echo isset($studyPlan['generated_at']) ? date('M j, Y', strtotime($studyPlan['generated_at'])) : date('M j, Y'); ?></strong>
                        <span>Generated</span>
                    </div>
                    <div class="meta-item">
                        <strong><?php echo isset($studyPlan['generated_by']) ? $studyPlan['generated_by'] : 'AI System'; ?></strong>
                        <span>System</span>
                    </div>
                </div>
            </div>

            <div class="plan-content">
                <?php if (isset($studyPlan['user_profile'])): ?>
                <div class="section">
                    <h3>
                        <span class="section-icon">👤</span>
                        Your Learning Profile
                    </h3>
                    <div class="profile-grid">
                        <div class="profile-card">
                            <h4>User Type</h4>
                            <p><?php echo htmlspecialchars($studyPlan['user_profile']['user_type'] ?? 'Not specified'); ?></p>
                        </div>
                        <div class="profile-card">
                            <h4>Optimal Study Time</h4>
                            <p><?php echo htmlspecialchars($studyPlan['user_profile']['optimal_study_time'] ?? 'Not specified'); ?></p>
                        </div>
                        <div class="profile-card">
                            <h4>Study Intensity</h4>
                            <p><?php echo htmlspecialchars($studyPlan['user_profile']['study_intensity'] ?? 'Not specified'); ?></p>
                        </div>
                        <div class="profile-card">
                            <h4>Consistency Level</h4>
                            <p><?php echo htmlspecialchars($studyPlan['user_profile']['consistency_level'] ?? 'Not specified'); ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (isset($studyPlan['recommendations']) && !empty($studyPlan['recommendations'])): ?>
                <div class="section">
                    <h3>
                        <span class="section-icon">💡</span>
                        Personalized Recommendations
                    </h3>
                    <ul class="recommendations-list">
                        <?php foreach ($studyPlan['recommendations'] as $recommendation): ?>
                            <li><?php echo htmlspecialchars($recommendation); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (isset($studyPlan['focus_areas']) && !empty($studyPlan['focus_areas'])): ?>
                <div class="section">
                    <h3>
                        <span class="section-icon">🎯</span>
                        Priority Focus Areas
                    </h3>
                    <ul class="focus-areas-list">
                        <?php foreach ($studyPlan['focus_areas'] as $area): ?>
                            <li><?php echo htmlspecialchars($area); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (isset($studyPlan['goals']) && !empty($studyPlan['goals'])): ?>
                <div class="section">
                    <h3>
                        <span class="section-icon">🏆</span>
                        Study Goals
                    </h3>
                    <ul class="focus-areas-list">
                        <?php foreach ($studyPlan['goals'] as $goal): ?>
                            <li><?php echo htmlspecialchars($goal); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (isset($studyPlan['daily_schedule']) && !empty($studyPlan['daily_schedule'])): ?>
                <div class="section">
                    <h3>
                        <span class="section-icon">📅</span>
                        Suggested Daily Schedule
                    </h3>
                    <div class="profile-grid">
                        <?php foreach ($studyPlan['daily_schedule'] as $timeSlot => $activity): ?>
                            <div class="profile-card">
                                <h4><?php echo htmlspecialchars($timeSlot); ?></h4>
                                <p><?php echo htmlspecialchars($activity); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <a href="questionnaire.php" class="btn btn-secondary">Take Another Assessment</a>
                    <button onclick="window.print()" class="btn btn-primary">Print Study Plan</button>
                    <button onclick="saveStudyPlan()" class="btn btn-primary">Save as PDF</button>
                </div>
            </div>
        </div>

        <?php endif; ?>
    </div>

    <script>
        function saveStudyPlan() {
            // Simple implementation - in a real Laravel app, this would generate a proper PDF
            const studyPlanContent = document.querySelector('.study-plan').innerHTML;
            const newWindow = window.open('', '', 'width=800,height=600');
            newWindow.document.write(`
                <html>
                    <head>
                        <title>Study Plan - Student #<?php echo isset($userId) ? $userId : 'N/A'; ?></title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .plan-header { background: #4CAF50; color: white; padding: 20px; margin-bottom: 20px; }
                            .section { margin-bottom: 20px; }
                            .section h3 { color: #4CAF50; border-bottom: 2px solid #4CAF50; padding-bottom: 5px; }
                            .profile-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
                            .profile-card { border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
                            .recommendations-list, .focus-areas-list { list-style-type: disc; margin-left: 20px; }
                            .action-buttons { display: none; }
                        </style>
                    </head>
                    <body>
                        ${studyPlanContent}
                    </body>
                </html>
            `);
            newWindow.document.close();
            newWindow.print();
        }

        // Clear session data after display (optional)
        <?php 
        // Uncomment the following lines if you want to clear the session after displaying
        // unset($_SESSION['study_plan']);
        // unset($_SESSION['user_id']);
        ?>
    </script>
</body>
</html>
