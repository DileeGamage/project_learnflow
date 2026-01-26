<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Note;

class NotesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sampleNotes = [
            [
                'user_id' => 1,
                'title' => 'Introduction to Algebra',
                'subject_area' => 'Mathematics',
                'content' => '<h2>Basic Algebra Concepts</h2><p>Algebra is a branch of mathematics dealing with symbols and the rules for manipulating those symbols.</p><ul><li>Variables: letters that represent unknown numbers</li><li>Constants: fixed numerical values</li><li>Expressions: combinations of variables and constants</li></ul>',
                'tags' => ['algebra', 'basics', 'mathematics'],
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'user_id' => 1,
                'title' => 'Photosynthesis Process',
                'subject_area' => 'Biology',
                'content' => '<h2>How Plants Make Food</h2><p>Photosynthesis is the process by which green plants use sunlight to synthesize foods from carbon dioxide and water.</p><p><strong>Chemical Equation:</strong><br>6CO₂ + 6H₂O + light energy → C₆H₁₂O₆ + 6O₂</p>',
                'tags' => ['photosynthesis', 'plants', 'biology'],
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
            [
                'user_id' => 1,
                'title' => 'World War II Timeline',
                'subject_area' => 'History',
                'content' => '<h2>Major Events of WWII</h2><ul><li><strong>1939:</strong> Germany invades Poland</li><li><strong>1941:</strong> Pearl Harbor attack</li><li><strong>1944:</strong> D-Day invasion</li><li><strong>1945:</strong> War ends</li></ul>',
                'tags' => ['wwii', 'timeline', 'history'],
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'user_id' => 1,
                'title' => 'Python Loops',
                'subject_area' => 'Programming',
                'content' => '<h2>For and While Loops</h2><p>Loops allow you to repeat code blocks.</p><h3>For Loop Example:</h3><pre><code>for i in range(5):\n    print(i)</code></pre><h3>While Loop Example:</h3><pre><code>i = 0\nwhile i < 5:\n    print(i)\n    i += 1</code></pre>',
                'tags' => ['python', 'loops', 'programming'],
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'user_id' => 1,
                'title' => 'English Grammar - Tenses',
                'subject_area' => 'English',
                'content' => '<h2>Understanding Verb Tenses</h2><p>English has three main tenses, each with four aspects:</p><h3>Present Tense:</h3><ul><li>Simple: I write</li><li>Continuous: I am writing</li><li>Perfect: I have written</li><li>Perfect Continuous: I have been writing</li></ul>',
                'tags' => ['grammar', 'tenses', 'english'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($sampleNotes as $noteData) {
            Note::create($noteData);
        }
    }
}
