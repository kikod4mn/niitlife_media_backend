<?php

declare(strict_types = 1);

namespace App\Service\EntityService;

use App\Entity\ImageComment;
use App\Service\EntityService\AbstractService\AbstractService;
use App\Service\EntityService\Contracts\AbstractServiceInterface;
use App\Support\Str;

class ImageCommentService extends AbstractService implements AbstractServiceInterface
{
	/**
	 * @var string
	 */
	const ENTITY = ImageComment::class;
	
	/**
	 * @return array
	 */
	public static function getProps(): array
	{
		return [
			
			'body' => [
				
				'callbacks' => [
					function (string $body) {
						return Str::purify($body);
					},
				],
			],
		
		];
	}
}