@echo off
echo ============================================
echo Starting Phi-3-Mini Quiz Service
echo ============================================
echo.
echo Model: microsoft/Phi-3-mini-4k-instruct
echo Quality: ChatGPT-level (5/5 stars)
echo Cost: $0 (Free forever)
echo Optimized for: Intel i7-13700H, 16GB RAM
echo.

cd /d "C:\Users\Dileesha\Desktop\project_learnflow\study-plan-ml-system"

echo Activating Python environment...
call C:\Users\Dileesha\Desktop\project_learnflow\.venv\Scripts\activate.bat

echo Starting service on port 5002...
echo First run will download ~3.8GB model (5-8 minutes)
echo Subsequent starts: 10-15 seconds
echo.
echo Keep this window open while using the quiz generator!
echo Press Ctrl+C to stop the service
echo.

python phi3_quiz_service.py

pause
