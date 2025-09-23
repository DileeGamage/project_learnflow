# Simple FYP Project Cleanup Script
# Run this to remove unnecessary files with confirmation

Write-Host "=== FYP Project Files Cleanup ===" -ForegroundColor Green
Write-Host ""

# Navigate to FYP directory
Set-Location "C:\Users\Dileesha\Desktop\FYP"

# Show what will be removed
Write-Host "Files that will be REMOVED:" -ForegroundColor Red
Write-Host "Main Directory:" -ForegroundColor Yellow
Write-Host "- basic_quiz_service.py" -ForegroundColor Red
Write-Host "- fast_free_quiz_service.py" -ForegroundColor Red
Write-Host "- simple_test_service.py" -ForegroundColor Red
Write-Host "- test_basic_service.py" -ForegroundColor Red
Write-Host "- test_document.txt" -ForegroundColor Red
Write-Host "- test_request.json" -ForegroundColor Red
Write-Host "- test-content.txt" -ForegroundColor Red
Write-Host ""

Write-Host "ML System Directory (study-plan-ml-system):" -ForegroundColor Yellow
$mlFilesToRemove = @(
    "demo_3layer_system.py", "demo_complete_system.py", "demo_full_output.txt",
    "demo_output.txt", "generate_study_plan_simple.py", "predict_questionnaire.py",
    "process_questionnaire.php", "questionnaire.php", "quiz_ml_service_simple.py",
    "results.php", "run_python.bat", "setup.py", "simple_predict.py",
    "simple_quiz_requirements.txt", "simple_quiz_service.py", "start_simple_quiz.bat",
    "student_study_habits_exam.csv", "study_plan_user_123.json", "test_knowledge_mastery.py",
    "test_predict.py"
)

foreach ($file in $mlFilesToRemove) {
    Write-Host "- $file" -ForegroundColor Red
}

Write-Host ""
Write-Host "Files that will be KEPT:" -ForegroundColor Green
Write-Host "Main Directory:" -ForegroundColor Yellow
Write-Host "+ enhanced_free_quiz_service.py" -ForegroundColor Green
Write-Host "+ pdf-ocr-service/" -ForegroundColor Green
Write-Host "+ study-plan-ml-system/" -ForegroundColor Green
Write-Host "+ study-plan-ml-system-laravel/" -ForegroundColor Green
Write-Host "+ uploads/" -ForegroundColor Green

Write-Host ""
Write-Host "ML System Directory:" -ForegroundColor Yellow
Write-Host "+ enhanced_free_quiz_service.py" -ForegroundColor Green
Write-Host "+ quiz_ml_service.py" -ForegroundColor Green
Write-Host "+ setup_free_ai.bat & setup_free_ai.sh" -ForegroundColor Green
Write-Host "+ requirements.txt files" -ForegroundColor Green
Write-Host "+ src/, venv_free_ai/, data/, models/, logs/" -ForegroundColor Green
Write-Host "+ .git/, README.md" -ForegroundColor Green

Write-Host ""
$response = Read-Host "Do you want to proceed with cleanup? (y/n)"

if ($response -eq 'y' -or $response -eq 'Y') {
    # Execute the main cleanup script
    & ".\cleanup_project.ps1"
} else {
    Write-Host "Cleanup cancelled." -ForegroundColor Yellow
    Write-Host "You can run .\cleanup_project.ps1 later when ready." -ForegroundColor Cyan
}