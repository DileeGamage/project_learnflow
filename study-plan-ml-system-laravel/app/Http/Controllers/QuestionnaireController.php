<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuestionnaireController extends Controller
{
    /**
     * Display the questionnaire assessment form
     */
    public function index()
    {
        return view('questionnaire');
    }

    /**
     * Process questionnaire data and generate study plan prediction
     */
    public function generatePrediction(Request $request): JsonResponse
    {
        try {
            // Validate the questionnaire data
            $validator = Validator::make($request->all(), [
                'age' => 'required|integer|min:16|max:35',
                'gender' => 'required|string|in:Male,Female,Prefer not to say',
                'study_hours_per_day' => 'required|integer|min:1|max:12',
                'revision_frequency' => 'required|string|in:Daily,Weekly,Before exams,Rarely',
                'preferred_study_time' => 'required|string|in:Morning,Afternoon,Evening,Night',
                'uses_online_learning' => 'required|string|in:Yes,No',
                'social_media_hours_per_day' => 'required|integer|min:0|max:8',
                'sleep_hours_per_day' => 'required|integer|min:4|max:12',
                'exam_stress_level' => 'required|integer|min:1|max:5',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $questionnaireData = $validator->validated();

            // Call Python ML model
            $prediction = $this->callPythonMLModel($questionnaireData);

            if (!$prediction) {
                throw new \Exception('Failed to get prediction from ML model');
            }

            // Generate study plan based on prediction
            $studyPlan = $this->generateStudyPlanFromPrediction($prediction, $questionnaireData);

            return response()->json([
                'success' => true,
                'predicted_exam_score' => $prediction['exam_score'],
                'performance_category' => $prediction['performance_category'] ?? $this->getPerformanceCategory($prediction['exam_score']),
                'insights' => $prediction['insights'] ?? $this->generateInsights($questionnaireData, $prediction),
                'recommendations' => $prediction['recommendations'] ?? $this->generateRecommendations($questionnaireData, $prediction),
                'study_plan' => $studyPlan
            ]);

        } catch (\Exception $e) {
            Log::error('Questionnaire prediction error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Unable to generate study plan. Please try again later.'
            ], 500);
        }
    }

    /**
     * Call the Python ML model for prediction
     */
    private function callPythonMLModel(array $data): ?array
    {
        try {
            // Path to the Python ML system - corrected path
            $pythonPath = base_path('..\study-plan-ml-system');
            $pythonScript = $pythonPath . '\simple_predict.py';

            // Check if Python script exists
            if (!file_exists($pythonScript)) {
                Log::error("Python script not found at: {$pythonScript}");
                return $this->fallbackPrediction($data);
            }

            // Prepare data for Python script - write to temp file to avoid quote issues
            $tempFile = tempnam(sys_get_temp_dir(), 'questionnaire_data_');
            file_put_contents($tempFile, json_encode($data));
            
            // Call Python script with file input - use absolute path to python
            $command = "python \"{$pythonScript}\" \"@{$tempFile}\"";
            
            // Add current working directory
            $cwd = dirname($pythonScript);
            
            // Execute command with proper working directory
            $descriptorspec = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ];
            
            $process = proc_open($command, $descriptorspec, $pipes, $cwd);
            
            if (is_resource($process)) {
                fclose($pipes[0]);
                $output = stream_get_contents($pipes[1]);
                $error = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $returnCode = proc_close($process);
                
                // Log any errors
                if ($error) {
                    Log::error("Python script error: {$error}");
                }
                
                if ($returnCode !== 0) {
                    Log::error("Python script returned code: {$returnCode}");
                }
            } else {
                Log::error("Failed to start Python process");
                return $this->fallbackPrediction($data);
            }

            // Clean up temp file
            unlink($tempFile);

            if (!$output) {
                Log::error('Python script returned no output');
                Log::error("Command used: {$command}");
                Log::error("Working directory: {$cwd}");
                return $this->fallbackPrediction($data);
            }

            $result = json_decode(trim($output), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Invalid JSON from Python script: ' . $output);
                return $this->fallbackPrediction($data);
            }

            // If Python returned an error, use fallback
            if (!$result['success']) {
                Log::error('Python script reported error: ' . $result['error']);
                return $this->fallbackPrediction($data);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error('Python ML model call failed: ' . $e->getMessage());
            
            // Fallback: use rule-based prediction
            return $this->fallbackPrediction($data);
        }
    }

    /**
     * Fallback prediction when ML model is unavailable
     */
    private function fallbackPrediction(array $data): array
    {
        // Simple rule-based prediction
        $baseScore = 65;
        
        // Study hours impact
        if ($data['study_hours_per_day'] >= 6) $baseScore += 10;
        elseif ($data['study_hours_per_day'] >= 4) $baseScore += 5;
        
        // Revision frequency impact
        if ($data['revision_frequency'] === 'Daily') $baseScore += 8;
        elseif ($data['revision_frequency'] === 'Weekly') $baseScore += 4;
        
        // Sleep impact
        if ($data['sleep_hours_per_day'] >= 7 && $data['sleep_hours_per_day'] <= 9) $baseScore += 5;
        elseif ($data['sleep_hours_per_day'] < 6) $baseScore -= 5;
        
        // Social media impact
        if ($data['social_media_hours_per_day'] > 4) $baseScore -= 5;
        elseif ($data['social_media_hours_per_day'] <= 2) $baseScore += 3;
        
        // Stress impact
        if ($data['exam_stress_level'] >= 4) $baseScore -= 3;
        
        // Online learning impact
        if ($data['uses_online_learning'] === 'Yes') $baseScore += 3;
        
        $finalScore = max(45, min(95, $baseScore));
        
        // Return complete prediction data structure
        return [
            'success' => true,
            'exam_score' => $finalScore,
            'performance_category' => $this->getPerformanceCategory($finalScore),
            'insights' => $this->generateInsights($data, ['exam_score' => $finalScore]),
            'recommendations' => $this->generateRecommendations($data, ['exam_score' => $finalScore])
        ];
    }

    /**
     * Get performance category based on exam score
     */
    private function getPerformanceCategory(float $score): string
    {
        if ($score >= 85) return 'Excellent Performance';
        if ($score >= 75) return 'Good Performance';
        if ($score >= 65) return 'Average Performance';
        return 'Needs Improvement';
    }

    /**
     * Generate insights based on user data and prediction
     */
    private function generateInsights(array $data, array $prediction): array
    {
        $insights = [];
        
        $insights[] = "Your predicted exam score is {$prediction['exam_score']}%";
        
        if ($data['study_hours_per_day'] >= 6) {
            $insights[] = "You have excellent study dedication with {$data['study_hours_per_day']} hours daily";
        } elseif ($data['study_hours_per_day'] < 3) {
            $insights[] = "Consider increasing your daily study time from {$data['study_hours_per_day']} hours";
        }
        
        if ($data['revision_frequency'] === 'Daily') {
            $insights[] = "Your daily revision habit is a strong foundation for success";
        } elseif ($data['revision_frequency'] === 'Rarely') {
            $insights[] = "Regular revision could significantly improve your performance";
        }
        
        if ($data['social_media_hours_per_day'] > 3) {
            $insights[] = "High social media usage ({$data['social_media_hours_per_day']} hours) may impact focus";
        }
        
        if ($data['sleep_hours_per_day'] < 6) {
            $insights[] = "Insufficient sleep ({$data['sleep_hours_per_day']} hours) affects learning capacity";
        }
        
        return $insights;
    }

    /**
     * Generate recommendations based on analysis
     */
    private function generateRecommendations(array $data, array $prediction): array
    {
        $recommendations = [];
        
        // Study time recommendations
        if ($data['study_hours_per_day'] < 4) {
            $recommendations[] = [
                'category' => 'Study Time',
                'priority' => 'High',
                'recommendation' => 'Increase daily study time to 4-6 hours for better performance',
                'impact' => 'Could improve exam score by 10-15%'
            ];
        }
        
        // Revision recommendations
        if ($data['revision_frequency'] !== 'Daily') {
            $recommendations[] = [
                'category' => 'Revision Strategy',
                'priority' => 'High',
                'recommendation' => 'Implement daily revision sessions for better retention',
                'impact' => 'Regular revision can boost performance by 8-12%'
            ];
        }
        
        // Sleep recommendations
        if ($data['sleep_hours_per_day'] < 7 || $data['sleep_hours_per_day'] > 9) {
            $recommendations[] = [
                'category' => 'Sleep Schedule',
                'priority' => 'Medium',
                'recommendation' => 'Maintain 7-9 hours of sleep for optimal cognitive function',
                'impact' => 'Better sleep improves focus and memory retention'
            ];
        }
        
        // Social media recommendations
        if ($data['social_media_hours_per_day'] > 3) {
            $recommendations[] = [
                'category' => 'Digital Wellness',
                'priority' => 'Medium',
                'recommendation' => 'Limit social media to 2 hours daily during study periods',
                'impact' => 'Reduced distractions can improve focus by 15-20%'
            ];
        }
        
        // Stress management
        if ($data['exam_stress_level'] >= 4) {
            $recommendations[] = [
                'category' => 'Stress Management',
                'priority' => 'High',
                'recommendation' => 'Practice relaxation techniques and maintain regular breaks',
                'impact' => 'Better stress management improves exam performance'
            ];
        }
        
        return $recommendations;
    }

    /**
     * Generate detailed study plan based on prediction and user preferences
     */
    private function generateStudyPlanFromPrediction(array $prediction, array $data): array
    {
        $studyHours = $data['study_hours_per_day'];
        $preferredTime = $data['preferred_study_time'];
        
        // Time slot mapping
        $timeSlots = [
            'Morning' => '6:00 AM - 12:00 PM',
            'Afternoon' => '12:00 PM - 6:00 PM', 
            'Evening' => '6:00 PM - 10:00 PM',
            'Night' => '10:00 PM - 2:00 AM'
        ];
        
        // Subject distribution based on study hours
        $subjects = $this->getSubjectDistribution($studyHours);
        
        // Weekly schedule
        $weeklySchedule = [];
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        foreach ($daysOfWeek as $day) {
            // Distribute subjects across the week
            $dailySubjects = $this->getDailySubjects($subjects, $day);
            $dailyHours = $this->getDailyHours($studyHours, $day);
            
            $weeklySchedule[$day] = [
                'subjects' => implode(', ', $dailySubjects),
                'study_hours' => $dailyHours . ' hours',
                'time_slot' => $timeSlots[$preferredTime]
            ];
        }
        
        return [
            'weekly_schedule' => $weeklySchedule,
            'total_weekly_hours' => $studyHours * 6, // 6 study days per week
            'focus_areas' => $this->getFocusAreas($prediction, $data),
            'study_techniques' => $this->getRecommendedTechniques($data),
            'daily_breakdown' => [
                'morning_routine' => $this->getMorningRoutine($data),
                'study_blocks' => $this->getStudyBlocks($studyHours),
                'break_schedule' => $this->getBreakSchedule($studyHours),
                'evening_review' => $this->getEveningReview($data)
            ]
        ];
    }

    private function getSubjectDistribution(int $studyHours): array
    {
        if ($studyHours <= 2) {
            return ['Core Subject 1', 'Core Subject 2'];
        } elseif ($studyHours <= 4) {
            return ['Core Subject 1', 'Core Subject 2', 'Elective 1'];
        } elseif ($studyHours <= 6) {
            return ['Core Subject 1', 'Core Subject 2', 'Elective 1', 'Elective 2'];
        } else {
            return ['Core Subject 1', 'Core Subject 2', 'Elective 1', 'Elective 2', 'Research/Project'];
        }
    }

    private function getDailySubjects(array $subjects, string $day): array
    {
        // Rotate subjects throughout the week
        $dayIndex = array_search($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
        $subjectCount = min(2, count($subjects));
        
        $dailySubjects = [];
        for ($i = 0; $i < $subjectCount; $i++) {
            $subjectIndex = ($dayIndex + $i) % count($subjects);
            $dailySubjects[] = $subjects[$subjectIndex];
        }
        
        return $dailySubjects;
    }

    private function getDailyHours(int $totalHours, string $day): string
    {
        // Sunday is lighter study day
        if ($day === 'Sunday') {
            return (string) max(1, intval($totalHours * 0.5));
        }
        
        return (string) $totalHours;
    }

    private function getFocusAreas(array $prediction, array $data): array
    {
        $focusAreas = [];
        
        if ($prediction['exam_score'] < 70) {
            $focusAreas[] = 'Foundation strengthening';
            $focusAreas[] = 'Regular practice sessions';
        }
        
        if ($data['revision_frequency'] !== 'Daily') {
            $focusAreas[] = 'Daily revision routine';
        }
        
        if ($data['exam_stress_level'] >= 4) {
            $focusAreas[] = 'Stress management techniques';
        }
        
        return $focusAreas ?: ['Maintaining current performance', 'Advanced topic exploration'];
    }

    private function getRecommendedTechniques(array $data): array
    {
        $techniques = [];
        
        if ($data['uses_online_learning'] === 'Yes') {
            $techniques[] = 'Interactive online modules';
            $techniques[] = 'Video tutorials and MOOCs';
        }
        
        if ($data['preferred_study_time'] === 'Morning') {
            $techniques[] = 'Complex problem solving in morning hours';
        } elseif ($data['preferred_study_time'] === 'Evening') {
            $techniques[] = 'Review and revision in evening sessions';
        }
        
        $techniques[] = 'Spaced repetition';
        $techniques[] = 'Active recall practices';
        
        if ($data['social_media_hours_per_day'] <= 2) {
            $techniques[] = 'Digital note-taking and organization';
        }
        
        return $techniques;
    }

    private function getMorningRoutine(array $data): array
    {
        $routine = [];
        
        if ($data['preferred_study_time'] === 'Morning') {
            $routine[] = 'Wake up 30 minutes earlier for preparation';
            $routine[] = 'Light exercise or stretching (10 minutes)';
            $routine[] = 'Healthy breakfast and hydration';
            $routine[] = 'Review previous day\'s notes (15 minutes)';
        } else {
            $routine[] = 'Consistent wake-up time for better sleep cycle';
            $routine[] = 'Morning meditation or mindfulness (10 minutes)';
            $routine[] = 'Nutritious breakfast to fuel the day';
        }
        
        return $routine;
    }

    private function getStudyBlocks(int $studyHours): array
    {
        $blocks = [];
        
        if ($studyHours <= 3) {
            $blocks[] = 'Single focused session (2-3 hours)';
            $blocks[] = 'Include 15-minute breaks every hour';
        } elseif ($studyHours <= 6) {
            $blocks[] = 'Morning session (2-3 hours)';
            $blocks[] = 'Afternoon/Evening session (2-3 hours)';
            $blocks[] = '30-minute break between sessions';
        } else {
            $blocks[] = 'Morning session (3 hours)';
            $blocks[] = 'Afternoon session (2-3 hours)';
            $blocks[] = 'Evening review session (1-2 hours)';
            $blocks[] = 'Longer breaks between intensive sessions';
        }
        
        return $blocks;
    }

    private function getBreakSchedule(int $studyHours): array
    {
        $schedule = [];
        
        $schedule[] = 'Pomodoro technique: 25 minutes study, 5 minutes break';
        $schedule[] = 'Longer break (15-30 minutes) every 2 hours';
        
        if ($studyHours > 4) {
            $schedule[] = 'Lunch break (45-60 minutes) mid-day';
            $schedule[] = 'Physical activity break (20 minutes)';
        }
        
        $schedule[] = 'Avoid screens during breaks when possible';
        $schedule[] = 'Stay hydrated and have healthy snacks';
        
        return $schedule;
    }

    private function getEveningReview(array $data): array
    {
        $review = [];
        
        if ($data['preferred_study_time'] === 'Evening' || $data['preferred_study_time'] === 'Night') {
            $review[] = 'Active study session in preferred evening time';
            $review[] = 'End with light review or planning for next day';
        } else {
            $review[] = 'Quick review of day\'s learning (20 minutes)';
            $review[] = 'Plan tomorrow\'s study goals';
        }
        
        $review[] = 'Reflection on progress and challenges';
        $review[] = 'Prepare materials for next day';
        
        if ($data['sleep_hours_per_day'] < 7) {
            $review[] = 'Wind down early to improve sleep duration';
        }
        
        return $review;
    }
}
