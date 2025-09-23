@echo off
cd /d "C:\Users\Dileesha\Desktop\FYP\study-plan-ml-system"
echo Starting Simple Quiz Service...
echo Service will be available at http://localhost:5002
echo Press Ctrl+C to stop the service
C:/Users/Dileesha/Desktop/FYP/.venv/Scripts/python.exe simple_quiz_service.py
pause