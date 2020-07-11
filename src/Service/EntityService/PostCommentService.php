<?php

declare(strict_types = 1);

namespace App\Service\EntityService;

use App\Entity\PostComment;
use App\Service\EntityService\AbstractService\AbstractService;
use App\Service\EntityService\Contracts\AbstractServiceInterface;
use App\Support\Str;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

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
						return Str::purify($body);
					},
				],
				
				'constraints' => [
					new NotBlank(
						[
							'message' => 'Comment body cannot be blank.',
						]
					),
					new Length(
						[
							'min'        => 10,
							'max'        => 4000,
							'minMessage' => 'Comment body must be at least {{ limit }} characters long.',
							'maxMessage' => 'Comment body maximum is {{ limit }} characters. Cool it Shakespeare!',
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