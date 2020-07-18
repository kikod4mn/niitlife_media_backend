<?php

declare(strict_types = 1);

namespace App\Tests\Service\EntityService;

use App\Entity\ImageComment;
use App\Entity\PostComment;
use App\Service\EntityService\Exception\ArrayKeyNotSetException;
use App\Service\EntityService\Exception\ClassConstantNotDefinedException;
use App\Service\EntityService\Exception\EmptyValueException;
use App\Service\EntityService\Exception\InvalidArrayKeysException;
use App\Service\EntityService\Exception\MethodNotFoundException;
use App\Service\EntityService\PostCommentService;
use App\Tests\Contracts\ExpectsExpections;
use App\Tests\Contracts\FindsFromRawOrValid;
use Faker\Provider\Lorem;
use Generator;
use LogicException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Exception\ValidatorException;

class PostCommentServiceTest extends TestCase implements ExpectsExpections
{
	use FindsFromRawOrValid;
	
	const NEW_COMMENT = 'NEW_COMMENT';
	
	protected array $validCreateData =
		[
			'body'       => 'This is the comments body. This is a valid body!!!',
			'test_field' => 'make_sure_array is not empty!!!',
		];
	
	protected array $validUpdateData =
		[
			'body'       => 'This is a valid edited body. Provide some comment for some value!',
			'test_field' => 'make_sure_array is not empty!!!',
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
		if ($expectation === self::NEW_COMMENT) {
			
			/** @var ImageComment $comment */
			$comment = PostCommentService::create($raw);
			
			self::assertTrue($comment instanceof PostComment);
			
			self::assertTrue($comment->isPublished());
			
			self::assertEquals(
				$this->validCreateData['body'],
				$comment->getBody()
			);
		}
		
		if ($expectation === self::EXCEPTION) {
			
			$this->expectException($exception);
			
			PostCommentService::create($raw);
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
		if ($expectation === self::NEW_COMMENT) {
			
			$comment = new PostComment();
			
			$comment->setBody($this->validCreateData['body']);
			
			$comment = PostCommentService::update($raw, $comment);
			
			self::assertTrue($comment instanceof PostComment);
			
			self::assertTrue($comment->isPublished());
			
			self::assertEquals(
				$this->getValueFromRaw('body', $raw),
				$comment->getBody()
			);
		}
		
		if ($expectation === self::EXCEPTION) {
			
			$this->expectException($exception);
			
			PostCommentService::update($raw, new PostComment());
		}
	}
	
	public function provideCreate(): Generator
	{
		yield 'valid data as string' => [
			'raw' => json_encode($this->validCreateData),
			self::NEW_COMMENT,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validCreateData,
			self::NEW_COMMENT,
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
			'raw' => [
				'body' => $this->validCreateData['body']
					. '<script>alert("asd")</script>',
			],
			self::NEW_COMMENT,
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
			'raw' => array_merge($this->validCreateData, ['body' => '']),
			self::EXCEPTION,
			EmptyValueException::class,
		];
		
		yield 'valid data, short body' => [
			'raw' => ['body' => '1s'],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long body' => [
			'raw' => ['body' => Lorem::text(8100)],
			self::EXCEPTION,
			ValidatorException::class,
		];
	}
	
	public function provideUpdate(): Generator
	{
		yield 'valid data as string' => [
			'raw' => json_encode($this->validUpdateData),
			self::NEW_COMMENT,
		];
		
		yield 'valid data as array' => [
			'raw' => $this->validUpdateData,
			self::NEW_COMMENT,
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
			'raw' => [
				'body' => $this->validUpdateData['body']
					. '<script>alert("asd")</script>',
			],
			self::NEW_COMMENT,
		];
		
		yield 'valid data, no body' => [
			'raw' => array_filter(
				$this->validUpdateData,
				fn($elem) => $elem !== $this->validUpdateData['body']
			),
			self::NEW_COMMENT,
		];
		
		yield 'valid data, blank body' => [
			'raw' => array_merge($this->validUpdateData, ['body' => '']),
			self::NEW_COMMENT,
		];
		
		yield 'valid data, short body' => [
			'raw' => ['body' => '1s'],
			self::EXCEPTION,
			ValidatorException::class,
		];
		
		yield 'valid data, long body' => [
			'raw' => ['body' => Lorem::text(8100)],
			self::EXCEPTION,
			ValidatorException::class,
		];
	}
}