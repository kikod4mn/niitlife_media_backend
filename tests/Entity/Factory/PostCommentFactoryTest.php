<?php

declare(strict_types = 1);

namespace App\Tests\Entity\Factory;

use App\Entity\Concerns\FilterProfanitiesTrait;
use App\Entity\Factory\Exception\ArrayKeyNotSetException;
use App\Entity\Factory\PostCommentFactory;
use App\Entity\PostComment;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PostCommentFactoryTest extends TestCase
{
	use FilterProfanitiesTrait;
	
	const NEW_COMMENT     = 'NEW_COMMENT';
	
	const UPDATED_COMMENT = 'UPDATED_COMMENT';
	
	const EXCEPTION       = 'EXCEPTION';
	
	/**
	 * @dataProvider provideTestCases
	 * @param               $data
	 * @param               $result
	 * @param  string       $cleanBody
	 * @param  string       $exception
	 */
	public function testMake(
		$data,
		$result,
		string $cleanBody = null,
		string $exception = null
	)
	{
		if ($result === self::NEW_COMMENT) {
			$comment = PostCommentFactory::make($data);
			
			self::assertTrue($comment instanceof PostComment);
			
			self::assertEquals($cleanBody, $comment->getBody());
		}
		
		if ($result === self::EXCEPTION && $exception) {
			$this->expectException($exception);
			
			PostCommentFactory::make($data);
		}
	}
	
	/**
	 * @dataProvider provideTestCases
	 * @param               $data
	 * @param               $result
	 * @param  null|string  $cleanBody
	 * @param  null|string  $exception
	 */
	public function testUpdate(
		$data,
		$result,
		string $cleanBody = null,
		string $exception = null
	)
	{
		// No need for full mock. Generate an empty Comment for testing.
		$commentMock = new PostComment();
		
		if ($result === self::NEW_COMMENT) {
			$comment = PostCommentFactory::update($data, $commentMock);
			
			self::assertTrue($comment instanceof PostComment);
			
			self::assertEquals($cleanBody, $comment->getBody());
		}
		
		if ($result === self::EXCEPTION && $exception) {
			$this->expectException($exception);
			
			PostCommentFactory::make($data);
		}
	}
	
	public
	function provideTestCases(): Generator
	{
		$body =
			"This is the 150 character body. This is a default body with no special stuff in it. Append some crap at the end for checking of tag or js removal. AWESOME!!!! So get on with it my friend! This is the 150 character body. This is a default body with no special stuff in it. Append some crap at the end for checking of tag or js removal. AWESOME!!!! So get on with it my friend! This is the 150 character body. This is a default body with no special stuff in it. Append some crap at the end for checking of tag or js removal. AWESOME!!!! So get on with it my friend!";
		
		$javascript = '<script>alert("hi fucko!!!")</script><script src="PostFactoryTest.php"></script>';
		
		$forbiddenTags = '<iframe>asd</iframe><link rel="stylesheet" href="PostFactoryTest.php">';
		
		$onClicks = '<div onclick="alert(\'asd\')" onmouseenter="alert(\'asd\')" onmouseover="alert(\'asd\')" onmousemove="alert(\'asd\')">attrDiv</div>';
		
		yield 'correct data with title and body as string' => [
			json_encode(['body' => $body]),
			self::NEW_COMMENT,
			$this->cleanString($body),
		];
		
		yield 'correct data with title and body as array' => [
			['body' => $body],
			self::NEW_COMMENT,
			$this->cleanString($body),
		];
		
		yield 'array with no body' => [
			['category' => 'some category'],
			self::EXCEPTION,
			null,
			ArrayKeyNotSetException::class,
		];
		
		yield 'empty array' => [
			[],
			self::EXCEPTION,
			null,
			InvalidArgumentException::class,
		];
		
		yield 'empty string' => [
			'',
			self::EXCEPTION,
			null,
			InvalidArgumentException::class,
		];
		
		yield 'correct data with javascript in body' => [
			['body' => $body . $javascript],
			self::NEW_COMMENT,
			$this->cleanString($body),
			null,
		];
		
		yield 'correct data with forbidden tags in body' => [
			['body' => $body . $forbiddenTags],
			self::NEW_COMMENT,
			$this->cleanString($body) . 'asd',
			null,
		];
		
		yield 'correct data with onclick attributes in body' => [
			['body' => $body . $forbiddenTags . $onClicks,],
			self::NEW_COMMENT,
			$this->cleanString($body) . 'asd' . 'attrDiv',
			null,
		];
	}
}