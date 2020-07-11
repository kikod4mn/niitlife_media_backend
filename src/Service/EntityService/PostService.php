<?php

declare(strict_types = 1);

namespace App\Service\EntityService;

use App\Entity\Post;
use App\Service\EntityService\AbstractService\AbstractService;
use App\Service\EntityService\Contracts\AbstractServiceInterface;
use App\Support\Str;

class PostService extends AbstractService implements AbstractServiceInterface
{
	/**
	 * @var string
	 */
	const ENTITY = Post::class;
	
	/**
	 * @return array|array[]
	 */
	public static function getProps(): array
	{
		return [
			'title' => [
				'callbacks' => [
					function (string $title) {
						return Str::purify($title);
					},
				],
			],
			
			'body' => [
				'callbacks' => [
					function (string $body) {
						return Str::cleanse($body);
					},
				],
			],
		
		];
	}
}