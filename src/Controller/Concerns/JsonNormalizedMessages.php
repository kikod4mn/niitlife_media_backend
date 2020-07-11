<?php

declare(strict_types = 1);

namespace App\Controller\Concerns;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait JsonNormalizedMessages
{
	/**
	 * @param  int     $code
	 * @param  string  $message
	 * @return JsonResponse
	 */
	protected function jsonMessage(int $code, string $message): JsonResponse
	{
		return $this->json(['code' => $code, 'message' => $message], $code);
	}
	
	/**
	 * @param  array  $violations
	 * @return JsonResponse
	 */
	protected function jsonViolations(array $violations): JsonResponse
	{
		$messages = [];
		
		if (count($violations) > 0) {
			
			foreach ($violations as $violation) {
				
				$messages[$violation->getPropertyPath()] = $violation->getMessage();
			}
		}
		
		return $this->jsonMessage(Response::HTTP_BAD_REQUEST, json_encode($messages));
	}
}