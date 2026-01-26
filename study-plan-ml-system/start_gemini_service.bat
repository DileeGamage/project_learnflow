@echo off
echo ============================================
echo Starting Gemini AI Quiz Service
echo ============================================
echo.

cd /d "C:\Users\Dileesha\Desktop\project_learnflow\study-plan-ml-system"

set GEMINI_API_KEY=AIzaSyCSljwHiuEN-D_FmLMSmqyZ6Sv6OKdJrso

echo API Key configured
echo Starting service on port 5003...
echo.
echo Keep this window open while using the quiz generator!
echo Press Ctrl+C to stop the service
echo.

python gemini_quiz_service.py

pause
