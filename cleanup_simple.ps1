# Simple FYP Cleanup Script
Write-Host "=== Starting FYP Project Cleanup ===" -ForegroundColor Green

# Navigate to FYP directory
Set-Location "C:\Users\Dileesha\Desktop\FYP"

# Create backup directory
$backupDir = "cleanup_backup_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss")
New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
Write-Host "Created backup directory: $backupDir" -ForegroundColor Cyan

# Files to remove from main directory
$mainFiles = @(
    "basic_quiz_service.py",
    "fast_free_quiz_service.py",
    "simple_test_service.py", 
    "test_basic_service.py",
    "test_document.txt",
    "test_request.json",
    "test-content.txt"
)

Write-Host "`nRemoving files from main directory..." -ForegroundColor Yellow
foreach ($file in $mainFiles) {
    if (Test-Path $file) {
        Copy-Item $file -Destination $backupDir
        Remove-Item $file -Force
        Write-Host "✓ Removed: $file" -ForegroundColor Red
    }
}

# Files to remove from ML system directory
$mlFiles = @(
    "demo_3layer_system.py",
    "demo_complete_system.py",
    "demo_full_output.txt", 
    "demo_output.txt",
    "generate_study_plan_simple.py",
    "predict_questionnaire.py",
    "process_questionnaire.php",
    "questionnaire.php",
    "quiz_ml_service_simple.py",
    "results.php",
    "run_python.bat",
    "setup.py",
    "simple_predict.py",
    "simple_quiz_requirements.txt",
    "simple_quiz_service.py",
    "start_simple_quiz.bat",
    "student_study_habits_exam.csv",
    "study_plan_user_123.json",
    "test_knowledge_mastery.py",
    "test_predict.py"
)

Write-Host "`nRemoving files from ML system directory..." -ForegroundColor Yellow
$mlDir = "study-plan-ml-system"
$mlBackupDir = Join-Path $backupDir "study-plan-ml-system"
New-Item -ItemType Directory -Path $mlBackupDir -Force | Out-Null

foreach ($file in $mlFiles) {
    $filePath = Join-Path $mlDir $file
    if (Test-Path $filePath) {
        Copy-Item $filePath -Destination $mlBackupDir
        Remove-Item $filePath -Force
        Write-Host "✓ Removed: $mlDir\$file" -ForegroundColor Red
    }
}

Write-Host "`n=== Cleanup Complete! ===" -ForegroundColor Green
Write-Host "Removed files backed up to: $backupDir" -ForegroundColor Cyan

Write-Host "`nRemaining working files:" -ForegroundColor Green
Write-Host "✓ enhanced_free_quiz_service.py" -ForegroundColor Green
Write-Host "✓ pdf-ocr-service/" -ForegroundColor Green  
Write-Host "✓ study-plan-ml-system/" -ForegroundColor Green
Write-Host "✓ study-plan-ml-system-laravel/" -ForegroundColor Green
Write-Host "✓ uploads/" -ForegroundColor Green

Write-Host "`nIn ML System directory:" -ForegroundColor Green
$keepFiles = @("enhanced_free_quiz_service.py", "quiz_ml_service.py", "setup_free_ai.bat", "setup_free_ai.sh", "requirements.txt")
foreach ($file in $keepFiles) {
    $path = Join-Path $mlDir $file
    if (Test-Path $path) {
        Write-Host "✓ $mlDir\$file" -ForegroundColor Green
    }
}

Write-Host "`nCleanup completed successfully!" -ForegroundColor Green