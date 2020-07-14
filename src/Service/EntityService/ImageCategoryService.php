<?php

declare(strict_types = 1);

namespace App\Service\EntityService;

use App\Entity\ImageCategory;
use App\Service\EntityService\AbstractService\AbstractService;
use App\Service\EntityService\Contracts\AbstractServiceInterface;
use App\Support\Str;

class ImageCategoryService extends AbstractService implements AbstractServiceInterface
{
	/**
	 * @var string
	 */
	const ENTITY = ImageCategory::class;
	
	public static function getProps(): array
	{
		return [
			
			'title' => [
				
				'callbacks' => [
					function (string $title) {
						if ($title === '') {
							
							return null;
						}
						
						return Str::purify($title);
					},
				],
			
			],
		];
	}
}