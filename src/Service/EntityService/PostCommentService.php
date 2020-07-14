<?php

declare(strict_types = 1);

namespace App\Service\EntityService;

use App\Entity\PostComment;
use App\Service\EntityService\AbstractService\AbstractService;
use App\Service\EntityService\Contracts\AbstractServiceInterface;
use App\Support\Str;

class PostCommentService extends AbstractService implements AbstractServiceInterface
{
	/**
	 * @var string
	 */
	const ENTITY = PostComment::class;
	
	/**
	 * @return array
	 */
	public static function getProps(): array
	{
		return [
			
			'body' => [
				
				'callbacks' => [
					function (string $body) {
						if ($body === '') {
							
							return null;
						}
						
						return Str::purify($body);
					},
				],
			],
		
		];
	}
}