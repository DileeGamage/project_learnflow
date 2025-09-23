<?php
/**
 * Process Study Habits Questionnaire
 * Collects user data and integrates with ML system
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

class StudyHabitsProcessor {
    
    private $csvFile = 'student_study_habits_exam.csv';
    private $dataDirectory = 'data/user_submissions/';
    
    public function __construct() {
        // Create directories if they don't exist
        if (!is_dir($this->dataDirectory)) {
            mkdir($this->dataDirectory, 0755, true);
        }
    }
    
    /**
     * Process the questionnaire submission
     */
    public function processSubmission() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError('Invalid request method');
            return;
        }
        
        try {
            // Validate and sanitize input data
            $studentData = $this->validateAndSanitizeData($_POST);
            
            // Save data to CSV
            $userId = $this->saveToCSV($studentData);
            
            // Save individual JSON file
            $this->saveToJSON($studentData, $userId);
            
            // Generate study plan via Python ML system
            $studyPlan = $this->generateStudyPlan($userId, $studentData);
            
            // Redirect to results page with success
            $this->redirectToResults($userId, $studyPlan);
            
        } catch (Exception $e) {
            error_log("Study Habits Processing Error: " . $e->getMessage());
            $this->redirectWithError('An error occurred while processing your submission: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate and sanitize form data
     */
    private function validateAndSanitizeData($postData) {
        $required_fields = [
            'age', 'gender', 'study_hours_per_day', 'revision_frequency',
            'preferred_study_time', 'uses_online_learning', 'social_media_hours_per_day',
            'sleep_hours_per_day', 'exam_stress_level', 'last_exam_score_percent'
        ];
        
        $studentData = [];
        
        foreach ($required_fields as $field) {
            if (!isset($postData[$field]) || empty($postData[$field])) {
                throw new Exception("Missing required field: $field");
            }
            
            // Sanitize based on field type
            switch ($field) {
                case 'age':
                    $value = filter_var($postData[$field], FILTER_VALIDATE_INT, 
                        array("options" => array("min_range" => 16, "max_range" => 30)));
                    if ($value === false) {
                        throw new Exception("Invalid age. Must be between 16 and 30.");
                    }
                    break;
                    
                case 'study_hours_per_day':
                case 'social_media_hours_per_day':
                case 'sleep_hours_per_day':
                case 'exam_stress_level':
                    $value = filter_var($postData[$field], FILTER_VALIDATE_INT, 
                        array("options" => array("min_range" => 0, "max_range" => 10)));
                    if ($value === false) {
                        throw new Exception("Invalid value for $field");
                    }
                    break;
                    
                case 'last_exam_score_percent':
                    $value = filter_var($postData[$field], FILTER_VALIDATE_FLOAT, 
                        array("options" => array("min_range" => 0, "max_range" => 100)));
                    if ($value === false) {
                        throw new Exception("Invalid exam score. Must be between 0 and 100.");
                    }
                    break;
                    
                case 'gender':
                    $validGenders = ['Male', 'Female', 'Prefer not to say'];
                    if (!in_array($postData[$field], $validGenders)) {
                        throw new Exception("Invalid gender selection");
                    }
                    $value = $postData[$field];
                    break;
                    
                case 'revision_frequency':
                    $validFrequencies = ['Daily', 'Weekly', 'Before exams', 'Rarely'];
                    if (!in_array($postData[$field], $validFrequencies)) {
                        throw new Exception("Invalid revision frequency");
                    }
                    $value = $postData[$field];
                    break;
                    
                case 'preferred_study_time':
                    $validTimes = ['Morning', 'Afternoon', 'Evening', 'Night'];
                    if (!in_array($postData[$field], $validTimes)) {
                        throw new Exception("Invalid study time preference");
                    }
                    $value = $postData[$field];
                    break;
                    
                case 'uses_online_learning':
                    $validOptions = ['Yes', 'No'];
                    if (!in_array($postData[$field], $validOptions)) {
                        throw new Exception("Invalid online learning preference");
                    }
                    $value = $postData[$field];
                    break;
                    
                default:
                    $value = htmlspecialchars(trim($postData[$field]), ENT_QUOTES, 'UTF-8');
            }
            
            $studentData[$field] = $value;
        }
        
        // Add timestamp
        $studentData['submission_timestamp'] = date('Y-m-d H:i:s');
        
        return $studentData;
    }
    
    /**
     * Save data to CSV file
     */
    private function saveToCSV($studentData) {
        $csvExists = file_exists($this->csvFile);
        $handle = fopen($this->csvFile, 'a');
        
        if (!$handle) {
            throw new Exception("Cannot open CSV file for writing");
        }
        
        // Write header if file is new
        if (!$csvExists) {
            $headers = array_keys($studentData);
            fputcsv($handle, $headers);
        }
        
        // Write data
        fputcsv($handle, array_values($studentData));
        fclose($handle);
        
        // Generate user ID (line number in CSV)
        $lines = file($this->csvFile);
        $userId = count($lines) - 1; // Subtract 1 for header
        
        return $userId;
    }
    
    /**
     * Save individual user data as JSON
     */
    private function saveToJSON($studentData, $userId) {
        $filename = $this->dataDirectory . "student_data_$userId.json";
        $jsonData = json_encode($studentData, JSON_PRETTY_PRINT);
        
        if (file_put_contents($filename, $jsonData) === false) {
            throw new Exception("Cannot save JSON file");
        }
        
        return $filename;
    }
    
    /**
     * Generate study plan using Python ML system
     */
    private function generateStudyPlan($userId, $studentData) {
        try {
            // Add user ID to student data
            $studentData['user_id'] = $userId;
            
            // Convert to JSON
            $jsonData = json_encode($studentData);
            
            // Call Python script via batch file that activates virtual environment
            $command = "run_python.bat " . escapeshellarg($jsonData) . " 2>&1";
            
            // Execute command
            $output = shell_exec($command);
            
            // Debug: Log the output
            error_log("Python Script Output: " . $output);
            
            // Parse JSON output
            $result = json_decode($output, true);
            
            if ($result === null || (isset($result['error']) && $result['error'])) {
                // Log the error for debugging
                $errorMsg = isset($result['message']) ? $result['message'] : 'JSON decode failed: ' . $output;
                error_log("ML System Error: " . $errorMsg);
                
                // Return fallback plan
                return $this->createFallbackStudyPlan($studentData);
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Study Plan Generation Error: " . $e->getMessage());
            return $this->createFallbackStudyPlan($studentData);
        }
    }
    
    /**
     * Create Python script for study plan generation
     */
    private function createPythonGeneratorScript($userId, $studentData) {
        $jsonData = json_encode($studentData);
        
        return "
import sys
import os
import json
import pandas as pd

# Add the project root to Python path
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

try:
    from src.inference.study_plan_generator import StudyPlanGenerator
    
    # Student data from PHP
    student_data = json.loads('$jsonData')
    
    # Create DataFrame with student data
    df = pd.DataFrame([student_data])
    df['user_id'] = $userId
    
    # Generate comprehensive study plan with content recommendations
    generator = StudyPlanGenerator()
    study_plan = generator.generate_comprehensive_study_plan(
        user_id=$userId,
        user_data=df,
        plan_duration_days=21,
        goals=[
            'Improve study efficiency based on your habits',
            'Optimize performance using AI recommendations',
            'Build sustainable learning routine'
        ]
    )
    
    # Extract and structure the output for PHP
    output = {
        'user_id': study_plan.get('user_id', $userId),
        'generated_at': study_plan.get('generated_at'),
        'duration_days': study_plan.get('duration_days', 21),
        'user_profile': study_plan.get('user_profile', {}),
        'goals': study_plan.get('goals', []),
        
        # Extract content recommendations
        'content_recommendations': study_plan.get('content_recommendations', {}),
        
        # Extract key recommendations from content engine
        'recommendations': [],
        'focus_areas': [],
        'daily_schedule': {},
        'weekly_plan': {}
    }
    
    # Process content recommendations if available
    if 'content_recommendations' in study_plan:
        content = study_plan['content_recommendations']
        
        # Extract recommendations
        if 'additional_recommendations' in content:
            output['recommendations'] = content['additional_recommendations']
        
        # Extract subjects as focus areas
        if 'subject_recommendations' in content:
            subjects = content['subject_recommendations']
            for subject, details in subjects.items():
                focus_text = f'{subject}: {details.get(\"weekly_focus\", \"Study fundamentals\")}'
                output['focus_areas'].append(focus_text)
        
        # Extract weekly plan if available
        if 'weekly_study_plan' in content:
            output['weekly_plan'] = content['weekly_study_plan']
            
            # Create a sample daily schedule from Monday's plan
            monday_plan = content['weekly_study_plan'].get('Monday', [])
            for session in monday_plan:
                time_key = session.get('time', 'Study Time')
                activity = f'{session.get(\"subject\", \"Study\")} - {session.get(\"topic\", \"General\")}'
                output['daily_schedule'][time_key] = activity
        
        # Extract user profile info
        if 'user_profile' in content:
            profile = content['user_profile']
            output['user_profile'].update({
                'user_type': f'{profile.get(\"performance_level\", \"\")}_{profile.get(\"study_intensity\", \"\")}_{profile.get(\"optimal_time\", \"\")}_learner'.lower(),
                'optimal_study_time': profile.get('optimal_time', 'Afternoon'),
                'study_intensity': profile.get('study_intensity', 'Moderate'),
                'consistency_level': profile.get('consistency_level', 'Medium')
            })
    
    # Fallback recommendations if content recommendations are empty
    if not output['recommendations']:
        output['recommendations'] = [
            'Establish a consistent daily study routine',
            'Focus on your preferred study time for maximum efficiency',
            'Take regular breaks to maintain concentration',
            'Use active learning techniques like practice problems',
            'Review material regularly to improve retention'
        ]
    
    if not output['focus_areas']:
        output['focus_areas'] = [
            'Building effective study habits',
            'Improving time management skills',
            'Enhancing academic performance',
            'Developing consistent learning routine'
        ]
    
    # Output JSON result
    print(json.dumps(output, default=str))
    
except Exception as e:
    # Output error for PHP to handle
    error_response = {
        'error': True,
        'message': str(e),
        'fallback': True
    }
    print(json.dumps(error_response))
";
    }
    
    /**
     * Create fallback study plan if ML system fails
     */
    private function createFallbackStudyPlan($studentData) {
        $plan = [
            'user_id' => 'fallback',
            'generated_at' => date('Y-m-d H:i:s'),
            'duration_days' => 21,
            'user_profile' => [
                'user_type' => $this->determineUserType($studentData),
                'optimal_study_time' => $studentData['preferred_study_time'],
                'study_intensity' => $this->determineStudyIntensity($studentData),
                'consistency_level' => $this->determineConsistency($studentData)
            ],
            'recommendations' => $this->generateBasicRecommendations($studentData),
            'focus_areas' => $this->identifyFocusAreas($studentData),
            'generated_by' => 'PHP Fallback System'
        ];
        
        return $plan;
    }
    
    private function determineUserType($data) {
        $type = [];
        
        // Time preference
        $type[] = strtolower($data['preferred_study_time']) . '_learner';
        
        // Study intensity
        if ($data['study_hours_per_day'] >= 6) {
            $type[] = 'intensive_studier';
        } elseif ($data['study_hours_per_day'] <= 2) {
            $type[] = 'light_studier';
        } else {
            $type[] = 'moderate_studier';
        }
        
        // Consistency
        if ($data['revision_frequency'] === 'Daily') {
            $type[] = 'consistent_learner';
        } else {
            $type[] = 'flexible_learner';
        }
        
        return implode('_', $type);
    }
    
    private function determineStudyIntensity($data) {
        if ($data['study_hours_per_day'] >= 6) return 'High';
        if ($data['study_hours_per_day'] <= 2) return 'Low';
        return 'Medium';
    }
    
    private function determineConsistency($data) {
        return ($data['revision_frequency'] === 'Daily') ? 'High' : 'Medium';
    }
    
    private function generateBasicRecommendations($data) {
        $recommendations = [];
        
        // Study time optimization
        $recommendations[] = "Optimize your study sessions during {$data['preferred_study_time']} hours";
        
        // Study duration advice
        if ($data['study_hours_per_day'] < 3) {
            $recommendations[] = "Consider gradually increasing daily study time for better results";
        } elseif ($data['study_hours_per_day'] > 7) {
            $recommendations[] = "Take regular breaks to avoid burnout";
        }
        
        // Revision strategy
        if ($data['revision_frequency'] === 'Rarely') {
            $recommendations[] = "Implement regular revision schedule for better retention";
        }
        
        // Stress management
        if ($data['exam_stress_level'] >= 4) {
            $recommendations[] = "Focus on stress management techniques and relaxation";
        }
        
        // Sleep optimization
        if ($data['sleep_hours_per_day'] < 7) {
            $recommendations[] = "Improve sleep schedule for better cognitive performance";
        }
        
        // Digital wellness
        if ($data['social_media_hours_per_day'] > 3) {
            $recommendations[] = "Reduce social media time during study periods";
        }
        
        return $recommendations;
    }
    
    private function identifyFocusAreas($data) {
        $areas = [];
        
        if ($data['last_exam_score_percent'] < 60) {
            $areas[] = "Academic performance improvement";
        }
        
        if ($data['exam_stress_level'] >= 4) {
            $areas[] = "Stress management";
        }
        
        if ($data['revision_frequency'] === 'Rarely') {
            $areas[] = "Regular revision habits";
        }
        
        if ($data['sleep_hours_per_day'] < 7) {
            $areas[] = "Sleep optimization";
        }
        
        if (empty($areas)) {
            $areas[] = "Maintaining current performance";
        }
        
        return $areas;
    }
    
    /**
     * Redirect to results page
     */
    private function redirectToResults($userId, $studyPlan) {
        $_SESSION['study_plan'] = $studyPlan;
        $_SESSION['user_id'] = $userId;
        header('Location: results.php?id=' . $userId);
        exit();
    }
    
    /**
     * Redirect with error message
     */
    private function redirectWithError($message) {
        $_SESSION['error'] = $message;
        header('Location: questionnaire.php?error=1');
        exit();
    }
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $processor = new StudyHabitsProcessor();
    $processor->processSubmission();
} else {
    // Redirect back to questionnaire if accessed directly
    header('Location: questionnaire.php');
    exit();
}
?>
