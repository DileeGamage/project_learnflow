<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'pdf_ocr' => [
        'url' => env('PDF_OCR_SERVICE_URL', 'http://localhost:5000'),
        'timeout' => env('PDF_OCR_SERVICE_TIMEOUT', 30),
    ],

    'quiz_ml' => [
        'url' => env('QUIZ_ML_SERVICE_URL', 'http://localhost:5001'),
        'timeout' => env('QUIZ_ML_SERVICE_TIMEOUT', 30),
    ],

    'enhanced_free_quiz' => [
        'url' => env('ENHANCED_FREE_QUIZ_URL', 'http://localhost:5002'),
        'timeout' => env('ENHANCED_FREE_QUIZ_TIMEOUT', 60), // Longer timeout for AI processing
    ],

    'gemini_quiz' => [
        'url' => env('GEMINI_QUIZ_URL', 'http://localhost:5003'),
        'timeout' => env('GEMINI_QUIZ_TIMEOUT', 90), // Gemini may take longer for quality
        'api_key' => env('GEMINI_API_KEY', ''),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'), // Optional
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 2000),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    ],

];
