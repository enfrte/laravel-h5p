<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class H5PResponse extends Model
{
    protected $table = 'h5p_responses';
    
    protected $fillable = [
        'interaction_id',
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
     * Get the interaction this response belongs to
     */
    public function interaction(): BelongsTo
    {
        return $this->belongsTo(H5PInteraction::class, 'interaction_id');
    }
    
    /**
     * Store a question response
     */
    public static function storeResponse(
        int $interactionId,
        string $questionId,
        string $interactionType,
        $response,
        ?string $questionText = null,
        ?bool $isCorrect = null,
        $correctResponsesPattern = null
    ): self {
        return self::updateOrCreate(
            [
                'interaction_id' => $interactionId,
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
     * Get all responses for an interaction
     */
    public static function getByInteraction(int $interactionId)
    {
        return self::where('interaction_id', $interactionId)
            ->orderBy('created_at', 'asc')
            ->get();
    }
    
    /**
     * Get response statistics for an interaction
     */
    public static function getStatistics(int $interactionId): array
    {
        $responses = self::where('interaction_id', $interactionId)->get();
        
        $total = $responses->count();
        $correct = $responses->where('is_correct', true)->count();
        $incorrect = $responses->where('is_correct', false)->count();
        
        return [
            'total' => $total,
            'correct' => $correct,
            'incorrect' => $incorrect,
            'accuracy' => $total > 0 ? round(($correct / $total) * 100, 2) : 0
        ];
    }
}
