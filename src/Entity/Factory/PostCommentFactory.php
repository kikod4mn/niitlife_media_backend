<?php

declare(strict_types = 1);

namespace App\Entity\Factory;

use App\Entity\Concerns\FilterProfanitiesTrait;
use App\Entity\Factory\Concerns\BaseFactoryTrait;
use App\Entity\Factory\Contracts\BaseFactoryInterface;
use App\Entity\Factory\Exception\ArrayKeyNotSetException;
use App\Entity\PostComment;
use App\Support\Str;
use App\Support\Validate;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\ValidatorException;

class PostCommentFactory implements BaseFactoryInterface
{
	use BaseFactoryTrait, FilterProfanitiesTrait;
	
	/**
	 * @param  string|array  $data
	 * @return PostComment
	 */
	public static function make($data): PostComment
	{
		return self::new($data);
	}
	
	/**
	 * @param  string|array  $data
	 * @param  PostComment   $comment
	 * @return PostComment
	 */
	public static function update($data, $comment): PostComment
	{
		self::entityTypeCheck($comment);
		
		return self::modify($data, $comment);
	}
	
	/**
	 * @param  array  $data
	 * @throws ArrayKeyNotSetException
	 */
	public function validArrayKeys(array $data): void
	{
		if (! array_key_exists('body', $data)) {
			throw new ArrayKeyNotSetException('Key "body" not set on raw post data!');
		}
	}
	
	/**
	 * @param  array  $data
	 * @return PostComment
	 */
	public function create(array $data): PostComment
	{
		$comment = new PostComment();
		
		return $this->setBody($data['body'], $comment);
	}
	
	/**
	 * @param  array        $data
	 * @param  PostComment  $comment
	 * @return PostComment
	 */
	public function edit(array $data, $comment): PostComment
	{
		if (isset($data['body']) && ! Validate::blank($data['body'])) {
			
			$comment = $this->setBody($data['body'], $comment);
		}
		
		return $comment;
	}
	
	/**
	 * @param  string       $body
	 * @param  PostComment  $comment
	 * @return PostComment
	 */
	protected function setBody(string $body, PostComment $comment): PostComment
	{
		$body = Str::purify($body);
		
		$error = $this->getValidator()->validate(
			$body,
			[
				new NotBlank(),
				new Length(
					[
						'min'        => 10,
						'max'        => 4000,
						'minMessage' => 'Comment body must be at least {{ limit }} characters long.',
						'maxMessage' => 'Comment body maximum is {{ limit }} characters. Cool it Shakespeare!',
					]
				),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		return $comment->setBody($this->filterProfanities($body));
	}
	
	/**
	 * @param $entity
	 */
	private static function entityTypeCheck($entity): void
	{
		if (! $entity instanceof PostComment) {
			
			throw new InvalidArgumentException('When updating, entity must be an instance of PostComment.');
		}
	}
}