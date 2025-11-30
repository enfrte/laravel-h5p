<?php

namespace App\Services;

use App\Models\H5PInteraction;
use App\Models\H5PResponse;

// Service to parse H5P response data and store individual question responses
class H5PResponseParser
{

    // Get student_id and content_id from headers, query params, or session
    public function extractIds($request): array
    {
        $studentId = $request->input('H5PIntegration')['user']['userId']
            ?: ($request->hasSession() ? $request->session()->get('temp_username') : null) // currently don't know how to access session in larvel in api context
            ?: 'unknown-student';
            
        $contentId = $request->input('H5PIntegration')['url']
            ?: 'unknown-content';
        
        return [$studentId, $contentId];
    }

    // Handle 'data' field which may be JSON string or array
    public function handleDataField($request): array
    {
        $data = $request->input();
        return $this->recursiveJsonDecode($data);
    }

    /**
     * Recursively decode JSON strings at any nesting level
     */
    protected function recursiveJsonDecode(mixed $data): mixed
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->recursiveJsonDecode($decoded);
            }
            return $data;
        }
        
        if (is_array($data)) {
            return array_map([$this, 'recursiveJsonDecode'], $data);
        }
        
        return $data;
    }

    /**
     * Parse H5P data and store individual question responses
     */
    public function parseAndStore(H5PInteraction $interaction, array $data): void
    {
        // H5P Interactive Book structure
        if (isset($data['chapters']) && is_array($data['chapters'])) {
            $this->parseInteractiveBook($interaction, $data['chapters']);
        }
        
        // Generic H5P xAPI statements (if present)
        if (isset($data['statements']) && is_array($data['statements'])) {
            $this->parseXAPIStatements($interaction, $data['statements']);
        }
    }
    
    /**
     * Parse H5P Interactive Book chapter responses
     */
    protected function parseInteractiveBook(H5PInteraction $interaction, array $chapters): void
    {
        foreach ($chapters as $chapterIndex => $chapter) {
            if (!isset($chapter['state']['instances'])) {
                continue;
            }
            
            foreach ($chapter['state']['instances'] as $instanceIndex => $instance) {
                // Skip empty instances
                if (empty($instance)) {
                    continue;
                }
                
                $questionId = "chapter-{$chapterIndex}-instance-{$instanceIndex}";
                
                H5PResponse::storeResponse(
                    interactionId: $interaction->id,
                    questionId: $questionId,
                    interactionType: $this->determineInteractionType($instance),
                    response: $this->extractResponse($instance),
                    questionText: $instance['question'] ?? $instance['text'] ?? null,
                    isCorrect: $this->determineCorrectness($instance),
                    correctResponsesPattern: $this->extractCorrectPattern($instance)
                );
            }
        }
    }
    
    /**
     * Parse xAPI statements (standard H5P format)
     */
    protected function parseXAPIStatements(H5PInteraction $interaction, array $statements): void
    {
        foreach ($statements as $statement) {
            if (!isset($statement['object']['id'])) {
                continue;
            }
            
            H5PResponse::storeResponse(
                interactionId: $interaction->id,
                questionId: $statement['object']['id'],
                interactionType: $statement['object']['definition']['interactionType'] ?? 'unknown',
                response: $statement['result']['response'] ?? null,
                questionText: $statement['object']['definition']['description']['en-US'] ?? null,
                isCorrect: $statement['result']['success'] ?? null,
                correctResponsesPattern: $statement['object']['definition']['correctResponsesPattern'] ?? null
            );
        }
    }
    
    /**
     * Determine interaction type from instance data
     */
    protected function determineInteractionType(array $instance): string
    {
        // True/False questions
        if (isset($instance['answer']) && is_bool($instance['answer'])) {
            return 'true-false';
        }
        
        // Multiple choice (single answer)
        if (isset($instance['answer']) && is_string($instance['answer'])) {
            return 'choice';
        }
        
        // Multiple choice (multiple answers)
        if (isset($instance['answers']) && is_array($instance['answers'])) {
            return 'multiple-choice';
        }
        
        // Fill in the blank
        if (isset($instance['response']) && is_string($instance['response'])) {
            return 'fill-in';
        }
        
        // Matching
        if (isset($instance['matches'])) {
            return 'matching';
        }
        
        return 'unknown';
    }
    
    /**
     * Extract student response from instance
     */
    protected function extractResponse(array $instance): mixed
    {
        return $instance['answer'] 
            ?? $instance['answers'] 
            ?? $instance['response'] 
            ?? $instance['matches'] 
            ?? null;
    }
    
    /**
     * Determine if response is correct
     */
    protected function determineCorrectness(array $instance): ?bool
    {
        // Explicit correct field
        if (isset($instance['correct'])) {
            return (bool) $instance['correct'];
        }
        
        // Check if answer matches correctAnswer
        if (isset($instance['answer']) && isset($instance['correctAnswer'])) {
            return $instance['answer'] === $instance['correctAnswer'];
        }
        
        // Check success field
        if (isset($instance['success'])) {
            return (bool) $instance['success'];
        }
        
        return null;
    }
    
    /**
     * Extract correct answer pattern
     */
    protected function extractCorrectPattern(array $instance): mixed
    {
        return $instance['correctAnswer'] 
            ?? $instance['correctAnswers'] 
            ?? $instance['correctResponsesPattern'] 
            ?? null;
    }
}
