<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Plan ML System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 50px;
        }

        .header h1 {
            font-size: 3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .header p {
            font-size: 1.3rem;
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .feature-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .feature-card p {
            color: #6c757d;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-primary {
            background-color: #4a90e2;
            color: white;
            border: 2px solid #4a90e2;
        }

        .btn-primary:hover {
            background-color: #357abd;
            border-color: #357abd;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 144, 226, 0.3);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: 2px solid #6c757d;
        }

        .btn-secondary:hover {
            background-color: #545b62;
            border-color: #545b62;
            transform: translateY(-2px);
        }

        .cta-section {
            background: #ffffff;
            padding: 50px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            text-align: center;
        }

        .cta-section h2 {
            font-size: 2.2rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .cta-section p {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .system-status {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 40px;
            text-align: center;
            border: 1px solid #b3d7ff;
        }

        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-healthy {
            background-color: #28a745;
        }

        .status-error {
            background-color: #dc3545;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Study Plan ML System</h1>
            <p>AI-Powered Personalized Learning Assistant that analyzes your study habits and creates tailored study plans for optimal academic performance.</p>
        </div>

        <div class="system-status" id="system-status">
            <strong>System Status:</strong> 
            <span id="status-text">
                <span class="status-indicator status-error"></span>
                Checking system connectivity...
            </span>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Study Habits Analysis</h3>
                <p>Complete assessment of your learning patterns, study hours, revision frequency, and lifestyle factors to build your unique learning profile.</p>
                <a href="/questionnaire" class="btn btn-primary">Take Assessment</a>
            </div>

            <div class="feature-card">
                <div class="feature-icon">🧠</div>
                <h3>AI-Powered Predictions</h3>
                <p>Machine learning algorithms analyze patterns from thousands of students to predict your academic performance and identify improvement areas.</p>
                <a href="/study-plan" class="btn btn-secondary">Quick Generator</a>
            </div>

            <div class="feature-card">
                <div class="feature-icon">📅</div>
                <h3>Personalized Schedule</h3>
                <p>Get a customized weekly study plan with optimal time slots, subject distribution, and break schedules based on your preferences.</p>
                <a href="/study-plan-demo" class="btn btn-secondary">View Demo</a>
            </div>
        </div>

        <div class="cta-section">
            <h2>Ready to Optimize Your Learning?</h2>
            <p>Take our comprehensive study habits assessment to receive personalized recommendations and boost your academic performance with data-driven insights.</p>
            
            <div class="cta-buttons">
                <a href="/questionnaire" class="btn btn-primary">📝 Start Assessment (5 min)</a>
                <a href="/study-plan" class="btn btn-secondary">⚡ Quick Study Plan</a>
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px; padding: 20px; color: #6c757d;">
            <p><strong>How it works:</strong> Complete questionnaire → AI analyzes patterns → Get personalized study plan → Improve performance</p>
        </div>
    </div>

    <script>
        // Check system status
        document.addEventListener('DOMContentLoaded', async function() {
            try {
                const response = await fetch('/api/study-plan/health');
                const data = await response.json();
                
                const statusElement = document.getElementById('status-text');
                if (data.ml_api_status === 'healthy') {
                    statusElement.innerHTML = `
                        <span class="status-indicator status-healthy"></span>
                        ✅ All systems operational - Ready to generate study plans!
                    `;
                } else {
                    statusElement.innerHTML = `
                        <span class="status-indicator status-error"></span>
                        ⚠️ ML API unavailable - Some features may be limited
                    `;
                }
            } catch (error) {
                document.getElementById('status-text').innerHTML = `
                    <span class="status-indicator status-error"></span>
                    ❌ System check failed - Please try again later
                `;
            }
        });
    </script>
</body>
</html>
