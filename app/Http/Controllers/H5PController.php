<?php

namespace App\Http\Controllers;

use App\Models\H5PInteraction;
use App\Models\H5PResponse;
use App\Services\H5PResponseParser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class H5PController extends Controller
{
	protected H5PResponseParser $responseParser;
	
	public function __construct(H5PResponseParser $responseParser)
	{
		$this->responseParser = $responseParser;
	}

	// Attempt to store bundled questions and state data from H5P
	public function recordQuestionAnswerCollection(Request $request): JsonResponse {
		[$studentId, $contentId] = $this->responseParser->extractIds($request);		
		$data = $this->responseParser->handleDataField($request);
		
		try {
			$interaction = DB::transaction(function () use ($studentId, $contentId, $data) {
				$interaction = H5PInteraction::recordInteraction(
					$studentId,
					$contentId,
					$data
				);

				// Parse and store individual question responses using service
				$this->responseParser->parseAndStore($interaction, $data);
			
				return $interaction;
			});

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
				'message' => 'Failed to record question answer collection',
				'error' => $e->getMessage()
			], 500);
		}
	}
	
	/**
	 * Record an H5P interaction from frontend
	 */
	public function recordInteraction(Request $request): JsonResponse
	{
		[$studentId, $contentId] = $this->responseParser->extractIds($request);
		$data = $this->responseParser->handleDataField($request);
		
		try {
			$interaction = DB::transaction(function () use ($studentId, $contentId, $data) {
				$interaction = H5PInteraction::recordInteraction(
					$studentId,
					$contentId,
					$data
				);
				
				return $interaction;
			});
			
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