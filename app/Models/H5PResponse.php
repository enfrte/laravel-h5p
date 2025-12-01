<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class H5PResponse extends Model
{
    protected $table = 'h5p_responses';
    
    protected $fillable = [
        'context_parent_id',
        'student',
        'course',
        'question_id',
        'question_text',
        'interaction_type',
        'is_correct',
        'response',
        'correct_responses_pattern'
    ];
    
    protected $casts = [
        'response' => 'array',
        'correct_responses_pattern' => 'array',
        'is_correct' => 'boolean',
    ];
    
    /**
     * Store a question response
     */
    public static function storeResponse(
        string $student,
        string $course,
        string $contextParentId,
        string $questionId,
        string $interactionType,
        $response,
        ?string $questionText = null,
        ?bool $isCorrect = null,
        $correctResponsesPattern = null
    ): self {
        // // Prevent overwriting a correct response with an incorrect one
        // $existing = self::where('student', $student)
        //     ->where('course', $course)
        //     ->where('context_parent_id', $contextParentId)
        //     ->where('question_id', $questionId)
        //     ->first();

        // if ($existing && $existing->is_correct) {
        //     return $existing;
        // }

        return self::updateOrCreate(
            [
                'student' => $student,
                'course' => $course,
                'context_parent_id' => $contextParentId,
                'question_id' => $questionId
            ],
            [
                'question_text' => $questionText,
                'interaction_type' => $interactionType,
                'response' => $response,
                'is_correct' => $isCorrect,
                'correct_responses_pattern' => $correctResponsesPattern
            ]
        );
    }
    
    /**
     * Get all responses for an interaction (optional student/course filters)
     */
    public static function getByInteraction(string $contextParentId, ?string $student = null, ?string $course = null)
    {
        $query = self::where('context_parent_id', $contextParentId);

        if ($student !== null) {
            $query->where('student', $student);
        }

        if ($course !== null) {
            $query->where('course', $course);
        }

        return $query->orderBy('created_at', 'asc')->get();
    }
}
