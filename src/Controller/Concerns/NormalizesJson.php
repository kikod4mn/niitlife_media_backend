<?php

declare(strict_types = 1);

namespace App\Controller\Concerns;

use Symfony\Component\HttpFoundation\JsonResponse;

trait NormalizesJson
{
	/**
	 * @param         $data
	 * @param  array  $groups
	 * @return JsonResponse
	 */
	protected function jsonNormalized($data, array $groups = []): JsonResponse
	{
		return $this->json($this->getSerializer()->normalize($data, null, ['groups' => $groups]));
	}
}