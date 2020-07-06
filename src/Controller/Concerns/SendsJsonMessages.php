<?php

declare(strict_types = 1);

namespace App\Controller\Concerns;

use Symfony\Component\HttpFoundation\JsonResponse;

trait SendsJsonMessages
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
}