@echo off
cd /d "C:\Users\Dileesha\Desktop\FYP\study-plan-ml-system"
call .venv\Scripts\activate.bat
python generate_study_plan_simple.py %1
