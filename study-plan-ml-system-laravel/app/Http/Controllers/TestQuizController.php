<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Quiz;
use App\Models\QuizAttempt;

class TestQuizController extends Controller
{
    public function testSubmit(Request $request)
    {
        try {
            Log::info('Test quiz submission', $request->all());
            
            // Simple test response
            return response()->json([
                'success' => true,
                'message' => 'Test submission successful',
                'data' => $request->all()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Test submission error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}