<?php

namespace App\Services;

use App\Models\H5PInteraction;
use App\Models\H5PResponse;
use Exception;
use Illuminate\Http\Request;
use App\Exceptions\SkipMissingData;

// Service to parse H5P response data and store individual question responses
class H5PResponseParser
{
	protected $data = [];
	protected ?string $interactionType;
	protected mixed $response = null;
	protected ?string $questionText = null;
	protected ?bool $isCorrect = null;
	protected mixed $correctResponsesPattern = null;

	// Handle 'data' field which may be JSON string or array
	public function handleDataField($request): void
	{
		$data = $request->input();
		$this->data = $this->recursiveJsonDecode($data);
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
	public function parseAndStore(Request $request): void
	{
		$this->handleDataField($request);
		$data = $this->data;

		// H5P Interactive Book structure
		// if (isset($data['chapters']) && is_array($data['chapters'])) {
		//     $this->parseInteractiveBook($data['chapters']);
		// }
		
		// Generic H5P xAPI statement (if present)
		if (isset($data['statement']) && is_array($data['statement'])) {
			$this->parseXAPIstatement();
		}
	}
	
	/**
	 * Parse H5P Interactive Book chapter responses
	 */
	protected function parseInteractiveBook(array $chapters): void
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
				
				// H5PResponse::storeResponse(
				//     interactionId: '$interaction->id', // replace me
				//     questionId: $questionId,
				//     interactionType: $this->determineInteractionType($instance),
				//     response: $this->extractResponse($instance),
				//     questionText: $instance['question'] ?? $instance['text'] ?? null,
				//     isCorrect: $this->determineCorrectness($instance),
				//     correctResponsesPattern: $this->extractCorrectPattern($instance)
				// );
			}
		}
	}

	
	/**
	 * Parse xAPI statement (standard H5P format)
	 */
	protected function parseXAPIstatement(): void
	{   
		H5PResponse::storeResponse(
			student: $this->extractStudentId(),
			course: $this->extractContentId(),
			contextParentId: $this->getInteractionId(),
			questionId: $this->getQuestionId(),
			interactionType: $this->getInteractionType(),
			response: $this->getResponse(),
			questionText: $this->getQuestionText(),
			isCorrect: $this->getIsCorrect(),
			correctResponsesPattern: $this->getCorrectResponsesPattern()
		);
	}

	// Extract student ID from data
	public function extractStudentId(): string
	{
		$student_id = $this->data['H5PIntegration']['user']['userId'];
		if ( empty($student_id) ) throw new SkipMissingData("No student id found");
		return $student_id;
	}

	// Extract content ID from data
	public function extractContentId(): string
	{
		$content_id = $this->data['H5PIntegration']['url'] ?? 'unknown-content';
		if ( empty($content_id) ) throw new SkipMissingData("No content id found");
		return $content_id;
	}

	protected function getInteractionType(): string
	{
		$type = $this->data['statement']['object']['definition']['interactionType'] ?? null;
		if ($type === null) throw new SkipMissingData("No interaction type found");
		return $type;
	}

	protected function getResponse(): mixed
	{
		$response = $this->data['statement']['result']['response'] ?? null;
		if ($response === null) throw new SkipMissingData("No response data found");
		return $response;
	}

	protected function getQuestionText(): ?string
	{
		$text = $this->data['statement']['object']['definition']['description']['en-US'] ?? null;
		if ($text === null) throw new SkipMissingData("No question text found");
		return $text;
	}

	protected function getIsCorrect(): ?bool
	{
		$correct = $this->data['statement']['result']['success'] ?? null;
		if ($correct === null) throw new SkipMissingData("No correctness data found");
		return $correct;
	}

	protected function getCorrectResponsesPattern(): mixed
	{
		$pattern = $this->data['statement']['object']['definition']['correctResponsesPattern'] ?? null;
		if ($pattern === null) throw new SkipMissingData("No correct response pattern found");
		return $pattern;
	}

	protected function getQuestionId(): string
	{
		$id = $this->data['statement']['object']['id'] ?? '';

		$parts = explode('subContentId=', $id);
		$id = $parts[1] ?? $id;

		if (empty($id)) throw new SkipMissingData("No question id found");

		return $id;
	}

	protected function getInteractionId(): string
	{
		$id = $this->data['statement']['context']['contextActivities']['parent'][0]['id'] ?? '';

		$parts = explode('subContentId=', $id);
		$id = $parts[1] ?? $id;

		if (empty($id)) throw new Exception("No interaction id found");

		return $id;
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
