<?php

declare(strict_types = 1);

namespace App\Entity\Factory;

use App\Entity\Factory\Concerns\BaseFactoryTrait;
use App\Entity\Factory\Contracts\BaseFactoryInterface;
use App\Entity\Factory\Exception\ArrayKeyNotSetException;
use App\Entity\Post;
use App\Support\Str;
use App\Support\Validate;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\ValidatorException;

class PostFactory implements BaseFactoryInterface
{
	use BaseFactoryTrait;
	
	/**
	 * @param $data
	 * @return Post
	 */
	public static function make($data): Post
	{
		return self::new($data);
	}
	
	/**
	 * @param $data
	 * @param $post
	 * @return Post
	 */
	public static function update($data, $post): Post
	{
		self::entityTypeCheck($post);
		
		return self::modify($data, $post);
	}
	
	/**
	 * @param  array  $data
	 * @return Post
	 */
	public function create(array $data): Post
	{
		$post = new Post();
		
		$post = $this->setTitle($data['title'], $post);
		$post = $this->setBody($data['body'], $post);
		
		return $post;
	}
	
	/**
	 * @param  array  $data
	 * @param  Post   $post
	 * @return Post
	 */
	public function edit(array $data, $post): Post
	{
		self::entityTypeCheck($post);
		
		if (isset($data['title']) && ! Validate::blank($data['title'])) {
			
			$post = $this->setTitle($data['title'], $post);
		}
		
		if (isset($data['body']) && ! Validate::blank($data['body'])) {
			
			$post = $this->setBody($data['body'], $post);
		}
		
		return $post;
	}
	
	/**
	 * @param  array  $data
	 * @throws ArrayKeyNotSetException
	 */
	public function validArrayKeys(array $data): void
	{
		if (! isset($data['title'])) {
			throw new ArrayKeyNotSetException('Key "title" not set on raw post data!');
		}
		
		if (! isset($data['body'])) {
			throw new ArrayKeyNotSetException('Key "body" not set on raw post data!');
		}
	}
	
	/**
	 * @param  string  $title
	 * @param  Post    $post
	 * @return Post
	 */
	protected function setTitle(string $title, Post $post): Post
	{
		$title = Str::purify($title);
		
		$error = $this->getValidator()->validate(
			$title,
			[
				new NotBlank(),
				new Length(
					[
						'min'        => 15,
						'minMessage' => 'Post title must be at least {{ limit }} characters long.',
						'max'        => 250,
						'maxMessage' => 'Post title must be not exceed {{ limit }} characters.',
					]
				),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		return $post->setTitle($title);
	}
	
	protected function setBody(string $body, Post $post): Post
	{
		$body = Str::cleanse($body);
		
		$error = $this->getValidator()->validate(
			$body,
			[
				new NotBlank(),
				new Length(
					[
						'min'        => 150,
						'minMessage' => 'Post body must be at least {{ limit }} characters long.',
					]
				),
			]
		)
		;
		
		if (count($error) > 0) {
			throw new ValidatorException((string) $error);
		}
		
		return $post->setBody($body);
	}
	
	/**
	 * @param $entity
	 */
	private static function entityTypeCheck($entity): void
	{
		if (! $entity instanceof Post) {
			
			throw new InvalidArgumentException('When updating, entity must be an instance of Post.');
		}
	}
}