<?php

declare(strict_types = 1);

namespace App\Service\EntityService;

use App\Entity\Image;
use App\Service\EntityService\AbstractService\AbstractService;
use App\Service\EntityService\Contracts\AbstractServiceInterface;
use App\Support\Str;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Url;

class ImageService extends AbstractService implements AbstractServiceInterface
{
	/**
	 * @var string
	 */
	const ENTITY = Image::class;
	
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
				
				'constraints' => [
					//					new NotBlank(
					//						[
					//							'message' => 'Image title cannot be blank.',
					//						]
					//					),
					new Length(
						[
							'min'        => 10,
							'minMessage' => 'Image title must be at least {{ limit }} characters long.',
							'max'        => 250,
							'maxMessage' => 'Image title must be not exceed {{ limit }} characters.',
						]
					),
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
				
				'constraints' => [
					//					new NotBlank(
					//						[
					//							'message' => 'Image description cannot be blank.',
					//						]
					//					),
					new Length(
						[
							'min'        => 20,
							'minMessage' => 'Image description must be at least {{ limit }} characters long.',
							'max'        => 65150,
							'maxMessage' => 'Image description must be not exceed {{ limit }} characters.',
						]
					),
				],
			
			],
			
			'original' => [
				
				'constraints' => [
					new NotBlank(
						[
							'message' => 'Image original URL cannot be blank.',
						]
					),
					new Url(
						[
							'message' => 'Please make sure the original of the image refers to a valid URL.',
						]
					),
				],
			
			],
			
			'half' => [
				
				'constraints' => [
					//					new NotBlank(
					//						[
					//							'message' => 'Image half URL cannot be blank.',
					//						]
					//					),
					new Url(
						[
							'message' => 'Please make sure the half of the image refers to a valid URL.',
						]
					),
				],
			
			],
			
			'thumbnail' => [
				
				'constraints' => [
					new NotBlank(
						[
							'message' => 'Image thumbnail URL cannot be blank.',
						]
					),
					new Url(
						[
							'message' => 'Please make sure the thumbnail of the image refers to a valid URL.',
						]
					),
				],
			
			],
		
		];
	}
	
	/**
	 * @param  array  $data
	 * @return null|array
	 */
	public static function rawConstraints(array $data)
	{
		return null;
	}
}