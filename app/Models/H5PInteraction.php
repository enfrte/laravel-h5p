<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class H5PInteraction extends Model
{
    protected $table = 'h5p_interactions';
    
    protected $fillable = [
        'student_id',
        'content_id',
        'interaction_data',
        'score',
        'max_score',
        'completed',
        'first_interaction_at',
        'completed_at'
    ];
    
    protected $casts = [
        'interaction_data' => 'array',
        'completed' => 'boolean',
        'first_interaction_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
    
    /**
     * Get all responses for this interaction
     */
    public function responses(): HasMany
    {
        return $this->hasMany(H5PResponse::class, 'interaction_id');
    }
    
    /**
     * Record or update an interaction
     */
    public static function recordInteraction(
        string $studentId, 
        string $contentId, 
        array $data
    ): self {
        $interaction = self::firstOrNew([
            'student_id' => $studentId,
            'content_id' => $contentId
        ]);
        
        // If already completed, don't update
        if ($interaction->completed) {
            return $interaction;
        }
        
        // First interaction
        if (!$interaction->exists) {
            $interaction->first_interaction_at = Carbon::now();
        }
        
        // Update interaction data
        $interaction->interaction_data = $data;
        
        // Check for score data
        if (isset($data['score'])) {
            $interaction->score = $data['score'];
        }
        if (isset($data['maxScore'])) {
            $interaction->max_score = $data['maxScore'];
        }
        
        // Check for completion - H5P Interactive Book specific
        $isCompleted = self::checkH5PCompletion($data);
        
        if ($isCompleted) {
            $interaction->completed = true;
            $interaction->completed_at = Carbon::now();
        }
        
        
        $interaction->save();
        
        return $interaction;
    }
    
    /**
     * Get all interactions with progress
     */
    public static function getAllProgress()
    {
        return self::orderBy('created_at', 'desc')->get();
    }
    
    /**
     * Get progress for a specific student
     */
    public static function getStudentProgress(string $studentId)
    {
        return self::where('student_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Check if H5P content is completed
     * Supports H5P Interactive Book and other content types
     */
    protected static function checkH5PCompletion(array $data): bool
    {
        // Method 1: Check if all chapters are completed (H5P Interactive Book)
        if (isset($data['chapters']) && is_array($data['chapters'])) {
            $allChaptersCompleted = true;
            foreach ($data['chapters'] as $chapter) {
                if (isset($chapter['completed']) && $chapter['completed'] === false) {
                    $allChaptersCompleted = false;
                    break;
                }
            }
            if ($allChaptersCompleted && count($data['chapters']) > 0) {
                return true;
            }
        }
        
        // Method 2: Check for explicit completed flag
        if (isset($data['completed']) && $data['completed'] === true) {
            return true;
        }
        
        // Method 3: Check if score equals maxScore (and both are set)
        if (isset($data['score']) && isset($data['maxScore']) 
            && $data['maxScore'] > 0 
            && $data['score'] >= $data['maxScore']) {
            return true;
        }
        
        return false;
    }
}