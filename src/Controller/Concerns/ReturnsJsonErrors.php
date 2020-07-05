<?php

declare(strict_types = 1);

namespace App\Controller\Concerns;

use Symfony\Component\HttpFoundation\JsonResponse;

trait ReturnsJsonErrors
{
	/**
	 * @param  int     $code
	 * @param  string  $message
	 * @return JsonResponse
	 */
	protected function jsonError(int $code, string $message): JsonResponse
	{
		return $this->json(['code' => $code, 'message' => $message], $code);
	}
}