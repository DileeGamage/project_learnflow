# FYP Project Cleanup Script
# This script removes test files and duplicates while keeping working files

Write-Host "=== FYP Project Cleanup Script ===" -ForegroundColor Green
Write-Host "This will remove unnecessary test files and duplicates" -ForegroundColor Yellow
Write-Host ""

# Get current directory
$currentDir = Get-Location
Set-Location "C:\Users\Dileesha\Desktop\FYP"

# Create backup directory
$backupDir = "backup_" + (Get-Date -Format "yyyy-MM-dd_HH-mm-ss")
New-Item -ItemType Directory -Path $backupDir -Force
Write-Host "Created backup directory: $backupDir" -ForegroundColor Cyan

# Files to remove from main FYP directory
$filesToRemoveMain = @(
    "basic_quiz_service.py",
    "fast_free_quiz_service.py", 
    "simple_test_service.py",
    "test_basic_service.py",
    "test_document.txt",
    "test_request.json",
    "test-content.txt"
)

# Files to remove from study-plan-ml-system directory
$filesToRemoveMLSystem = @(
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

Write-Host "=== Cleaning Main FYP Directory ===" -ForegroundColor Yellow

foreach ($file in $filesToRemoveMain) {
    $filePath = Join-Path (Get-Location) $file
    if (Test-Path $filePath) {
        # Backup file before removing
        Copy-Item $filePath -Destination $backupDir
        Remove-Item $filePath -Force
        Write-Host "✓ Removed: $file" -ForegroundColor Red
    } else {
        Write-Host "- File not found: $file" -ForegroundColor Gray
    }
}

Write-Host ""
Write-Host "=== Cleaning ML System Directory ===" -ForegroundColor Yellow

$mlSystemDir = "study-plan-ml-system"
if (Test-Path $mlSystemDir) {
    foreach ($file in $filesToRemoveMLSystem) {
        $filePath = Join-Path $mlSystemDir $file
        if (Test-Path $filePath) {
            # Backup file before removing
            $backupSubDir = Join-Path $backupDir "study-plan-ml-system"
            if (!(Test-Path $backupSubDir)) {
                New-Item -ItemType Directory -Path $backupSubDir -Force
            }
            Copy-Item $filePath -Destination $backupSubDir
            Remove-Item $filePath -Force
            Write-Host "✓ Removed: study-plan-ml-system\$file" -ForegroundColor Red
        } else {
            Write-Host "- File not found: study-plan-ml-system\$file" -ForegroundColor Gray
        }
    }
} else {
    Write-Host "ML System directory not found!" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Files Kept in Main Directory ===" -ForegroundColor Green
$keptFilesMain = @(
    "enhanced_free_quiz_service.py",
    "pdf-ocr-service/",
    "study-plan-ml-system/",
    "study-plan-ml-system-laravel/",
    "uploads/"
)

foreach ($item in $keptFilesMain) {
    if (Test-Path $item) {
        Write-Host "✓ Kept: $item" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "=== Files Kept in ML System Directory ===" -ForegroundColor Green
$keptFilesML = @(
    "enhanced_free_quiz_service.py",
    "quiz_ml_service.py",
    "setup_free_ai.bat",
    "setup_free_ai.sh", 
    "enhanced_free_requirements.txt",
    "requirements.txt",
    "quiz_service_requirements.txt",
    "README.md",
    "src/",
    "venv_free_ai/",
    "data/",
    "models/",
    "logs/",
    ".venv/",
    ".git/",
    ".gitignore"
)

foreach ($item in $keptFilesML) {
    $itemPath = Join-Path $mlSystemDir $item
    if (Test-Path $itemPath) {
        Write-Host "✓ Kept: study-plan-ml-system\$item" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "=== Documentation Files (Optional to keep) ===" -ForegroundColor Cyan
$docFiles = @(
    "AUTHENTICATION_SETUP_COMPLETE.md",
    "PHASE_1_COMPLETION_SUMMARY.md", 
    "RESEARCH_IMPLEMENTATION_ROADMAP.md",
    "USER_AUTHENTICATION_IMPLEMENTATION.md"
)

Write-Host "The following documentation files are still present:" -ForegroundColor Cyan
foreach ($doc in $docFiles) {
    if (Test-Path $doc) {
        Write-Host "- $doc (you can remove manually if not needed)" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "=== Cleanup Complete ===" -ForegroundColor Green
Write-Host "All removed files have been backed up to: $backupDir" -ForegroundColor Cyan
Write-Host "Your working files are preserved and organized." -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "1. Test your main service: enhanced_free_quiz_service.py" -ForegroundColor White
Write-Host "2. Verify your Laravel application in study-plan-ml-system-laravel/" -ForegroundColor White
Write-Host "3. Run setup scripts if needed: study-plan-ml-system/setup_free_ai.bat" -ForegroundColor White

# Return to original directory
Set-Location $currentDir