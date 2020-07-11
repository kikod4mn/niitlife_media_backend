<?php

declare(strict_types = 1);

namespace App\Service\EntityService;

use App\Entity\Post;
use App\Service\EntityService\AbstractService\AbstractService;
use App\Service\EntityService\Contracts\AbstractServiceInterface;
use App\Support\Str;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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
				
				'constraints' => [
					new NotBlank(
						[
							'message' => 'Post title cannot be blank.',
						]
					),
					new Length(
						[
							'min'        => 15,
							'minMessage' => 'Post title must be at least {{ limit }} characters long.',
							'max'        => 250,
							'maxMessage' => 'Post title must be not exceed {{ limit }} characters.',
						]
					),
				],
			],
			'body'  => [
				'callbacks' => [
					function (string $body) {
						return Str::cleanse($body);
					},
				],
				
				'constraints' => [
					new NotBlank(
						[
							'message' => 'Post body cannot be blank.',
						]
					),
					new Length(
						[
							'min'        => 20,
							'minMessage' => 'Post body must be at least {{ limit }} characters long.',
							'max'        => 65150,
							'maxMessage' => 'Post body must be not exceed {{ limit }} characters.',
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