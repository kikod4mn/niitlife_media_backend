<?php

declare(strict_types = 1);

namespace App\Service\EntityService;

use App\Entity\Image;
use App\Service\EntityService\AbstractService\AbstractService;
use App\Service\EntityService\Contracts\AbstractServiceInterface;
use App\Support\Str;

class ImageService extends AbstractService implements AbstractServiceInterface
{
	/**
	 * @var string
	 */
	const ENTITY = Image::class;
	
	protected static array $optionalFields = ['title', 'description'];
	
	/**
	 * @return array
	 */
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
			
			'description' => [
				
				'callbacks' => [
					function (string $description) {
						if ($description === '') {
							
							return null;
						}
						
						return Str::purify($description);
					},
				],
			
			],
			
			'original' => [],
			
			'half' => [],
			
			'thumbnail' => [],
		
		];
	}
}