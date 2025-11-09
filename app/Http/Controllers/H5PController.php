<?php

namespace App\Http\Controllers;

use App\Models\H5PInteraction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class H5PController extends Controller
{
    /**
     * Record an H5P interaction from frontend
     */
    public function recordInteraction(Request $request): JsonResponse
    {
        // Get student_id and content_id from headers, query params, or session
        $studentId = $request->header('X-Student-ID') 
            ?? $request->input('student_id') 
            ?? '111';
            
        $contentId = $request->header('X-Content-ID') 
            ?? $request->input('content_id')
            ?? $request->input('contextId')
            ?? 'default-content';
        
        // Handle data field - it might be a JSON string or already an array
        $data = $request->input('data');
        if (is_string($data)) {
            $data = json_decode($data, true);
        }
        
        // Validate we have valid data
        if (!$data || !is_array($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data format'
            ], 400);
        }
        
        try {
            //dd([$studentId,$contentId, $data]);

            $interaction = H5PInteraction::recordInteraction(
                $studentId,
                $contentId,
                $data
            );
            
            return response()->json([
                'success' => true,
                'message' => $interaction->completed 
                    ? 'Interaction already completed' 
                    : 'Interaction recorded',
                'data' => $interaction
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to record interaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display all student progress
     */
    public function showAllProgress()
    {
        $interactions = H5PInteraction::getAllProgress();
        
        return view('h5p.progress', [
            'interactions' => $interactions
        ]);
    }
    
    /**
     * Display progress for a specific student
     */
    public function showStudentProgress(string $studentId)
    {
        $interactions = H5PInteraction::getStudentProgress($studentId);
        
        return view('h5p.student-progress', [
            'student_id' => $studentId,
            'interactions' => $interactions
        ]);
    }
    
    /**
     * API endpoint to get all progress as JSON
     */
    public function getProgressJson(): JsonResponse
    {
        $interactions = H5PInteraction::getAllProgress();
        
        return response()->json([
            'success' => true,
            'data' => $interactions
        ]);
    }
}