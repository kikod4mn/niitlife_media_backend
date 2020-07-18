<?php

declare(strict_types = 1);

namespace App\Tests\Service\EntityService;

use App\Entity\Post;
use App\Service\EntityService\Exception\ArrayKeyNotSetException;
use App\Service\EntityService\Exception\ClassConstantNotDefinedException;
use App\Service\EntityService\Exception\EmptyValueException;
use App\Service\EntityService\Exception\InvalidArrayKeysException;
use App\Service\EntityService\Exception\MethodNotFoundException;
use App\Service\EntityService\PostService;
use App\Tests\Contracts\ExpectsExpections;
use App\Tests\Contracts\FindsFromRawOrValid;
use Faker\Provider\Lorem;
use Generator;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\ValidatorException;

class PostServiceTest extends TestCase implements ExpectsExpections
{
	use FindsFromRawOrValid;
	
	const NEW_POST = 'NEW_POST';
	
	protected array $validCreateData =
		[
			'title' => 'This is a new post title!!!',
			'body'  => '<h2>This is a posts body</h2><p>This is a paragraph of text</p>',
		];
	
	protected array $validUpdateData =
		[
			'title' => 'This is an edited post title! Completely different',
			'body'  => '<h2>This is an edited posts body</h2><p>This is a paragraph of completely different text</p>',
		];
	
	/**
	 * @dataProvider provideCreate
	 * @param          $raw
	 * @param  string  $expectation
	 * @param  string  $exception
	 * @throws ArrayKeyNotSetException
	 * @throws EmptyValueException
	 * @throws ClassConstantNotDefinedException
	 * @throws InvalidArrayKeysException
	 * @throws MethodNotFoundException
	 */
	public function testCreate($raw, string $expectation, string $exception = null)
	{
		if ($expectation === self::NEW_POST) {
			
			/** @var Post $post */
			$post = PostService::create($raw);
			
			self::assertTrue($post instanceof Post);
			
			self::assertTrue($post->isPublished());
			
			self::assertEquals(
				$this->validCreateData['title'],
				$post->getTitle()
			);
			
			self::assertEquals(
				$this->validCreateData['body'],
				$post->getBody()
			);
		}
		
		if ($expectation === self::EXCEPTION) {
			
			$this->expectException($exception);
			
			PostService::create($raw);
		}
	}
	
	/**
	 * @dataProvider provideUpdate
	 * @param          $raw
	 * @param  string  $expectation
	 * @param  string  $exception
	 * @throws InvalidArrayKeysException
	 * @throws MethodNotFoundException|EmptyValueException
	 */
	public function testUpdate($raw, string $expectation, string $exception = null)
	{
		if ($expectation === self::NEW_POST) {
			
			$post = new Post();
			
			$post->setTitle($this->validCreateData['title']);
			$post->setBody($this->validCreateData['body']);
			
			$post = PostService::update($raw, $post);
			
			self::assertTrue($post instanceof Post);
			
			self::assertTrue($post->isPublished());
			
			self::assertEquals(
				$this->getValueFromRaw('title', $raw),
				$post->getTitle()
			);
			
			self::assertEquals(
				$this->getValueFromRaw('body', $raw),
				$post->getBody()
			);
		}
		
		if ($expectation === self::EXCEPTION) {
			
			$this->expectException($exception);
			
			PostService::update($raw, new Post());
		}
	}
	
	/**
	 * @return Generator
	 */
	public function provideCreate(): Generator
	{
		yield 'valid data as string' => [
			'raw' => json_encode($this->validCreateData),
			self::NEW_POST,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validCreateData,
			self::NEW_POST,
		];
		
		yield 'invalid data type object' => [
			'raw' => (object) $this->validCreateData,
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'empty array' => [
			'raw' => [],
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'empty string' => [
			'raw' => '',
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'invalid title before purify' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'title' => $this->validCreateData['title']
						. '<script>alert("asd")</script>',
				]
			),
			self::NEW_POST,
		];
		
		yield 'invalid body before cleanse' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'body' => $this->validCreateData['body']
						. '<script>alert("asd")</script>',
				]
			),
			self::NEW_POST,
		];
		
		yield 'data with invalid title before purification and empty after' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'title' => '<script>alert("asd")</script>',
				]
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'data with invalid body before cleansing and empty after' => [
			'raw' => array_merge(
				$this->validCreateData,
				[
					'body' => '<script>alert("asd")</script>',
				]
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, no title' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['title']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank title' => [
			'raw' => array_merge(
				$this->validCreateData,
				['title' => '']
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, no body' => [
			'raw' => array_filter(
				$this->validCreateData,
				fn($elem) => $elem !== $this->validCreateData['body']
			),
			self::EXCEPTION,
			ArrayKeyNotSetException::class,
		];
		
		yield 'valid data, blank body' => [
			'raw' => array_merge(
				$this->validCreateData,
				['body' => '']
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, short title' => [
			'raw' => array_merge(
				$this->validCreateData,
				['title' => '1s']
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long title' => [
			'raw' => array_merge(
				$this->validCreateData,
				['title' => Lorem::text(1000)]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, short body' => [
			'raw' => array_merge(
				$this->validCreateData,
				['body' => '1s']
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long body' => [
			'raw' => array_merge(
				$this->validCreateData,
				['body' => Lorem::text(30000)]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
	}
	
	/**
	 * @return Generator
	 */
	public function provideUpdate(): Generator
	{
		yield 'valid data as string' => [
			'raw' => json_encode($this->validUpdateData),
			self::NEW_POST,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validUpdateData,
			self::NEW_POST,
		];
		
		yield 'invalid data type object' => [
			'raw' => (object) $this->validUpdateData,
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'empty array' => [
			'raw' => [],
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'empty string' => [
			'raw' => '',
			self::EXCEPTION,
			LogicException::class,
		];
		
		yield 'invalid title before purify' => [
			'raw' => array_merge(
				$this->validUpdateData,
				[
					'title' => $this->validUpdateData['title']
						. '<script>alert("asd")</script>',
				]
			),
			self::NEW_POST,
		];
		
		yield 'invalid body before cleanse' => [
			'raw' => array_merge(
				$this->validUpdateData,
				[
					'body' => $this->validUpdateData['body']
						. '<script>alert("asd")</script>',
				]
			),
			self::NEW_POST,
		];
		
		yield 'data with invalid title before purification and empty after' => [
			'raw' => array_merge(
				$this->validUpdateData,
				[
					'title' => '<script>alert("asd")</script>',
				]
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'data with invalid body before cleansing and empty after' => [
			'raw' => array_merge(
				$this->validUpdateData,
				[
					'body' => '<script>alert("asd")</script>',
				]
			),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, no title' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['title']
			),
			self::NEW_POST,
		];
		
		yield 'valid data, blank title' => [
			'raw' => array_merge(
				$this->validUpdateData,
				['title' => '']
			),
			self::NEW_POST,
		];
		
		yield 'valid data, no body' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['body']
			),
			self::NEW_POST,
		];
		
		yield 'valid data, blank body' => [
			'raw' => array_merge(
				$this->validUpdateData,
				['body' => '']
			),
			self::NEW_POST,
		];
		
		yield 'valid data, short title' => [
			'raw' => array_merge(
				$this->validUpdateData,
				['title' => '1s']
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long title' => [
			'raw' => array_merge(
				$this->validUpdateData,
				['title' => Lorem::text(1000)]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, short body' => [
			'raw' => array_merge(
				$this->validUpdateData,
				['body' => '1s']
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long body' => [
			'raw' => array_merge(
				$this->validUpdateData,
				['body' => Lorem::text(30000)]
			),
			self::EXCEPTION,
			ValidatorException::class,
		];
	}
}