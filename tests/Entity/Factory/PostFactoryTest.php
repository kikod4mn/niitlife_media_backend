<?php

declare(strict_types = 1);

namespace App\Tests\Entity\Factory;

use App\Entity\Factory\Exception\ArrayKeyNotSetException;
use App\Entity\Factory\PostFactory;
use App\Entity\Post;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PostFactoryTest extends TestCase
{
	const NEW_POST     = 'NEW_POST';
	
	const UPDATED_POST = 'UPDATED_POST';
	
	const EXCEPTION    = 'EXCEPTION';
	
	/**
	 * @dataProvider provideTestCases
	 * @param               $data
	 * @param               $result
	 * @param  string       $cleanTitle
	 * @param  string       $cleanBody
	 * @param  string       $exception
	 */
	public function testMake(
		$data,
		$result,
		string $cleanTitle = null,
		string $cleanBody = null,
		string $exception = null
	)
	{
		if ($result === self::NEW_POST) {
			$post = PostFactory::make($data);
			
			self::assertTrue($post instanceof Post);
			
			self::assertEquals($cleanTitle, $post->getTitle());
			self::assertEquals($cleanBody, $post->getBody());
		}
		
		if ($result === self::EXCEPTION && $exception) {
			$this->expectException($exception);
			
			PostFactory::make($data);
		}
	}
	
	/**
	 * @dataProvider provideTestCases
	 * @param               $data
	 * @param               $result
	 * @param  null|string  $cleanTitle
	 * @param  null|string  $cleanBody
	 * @param  null|string  $exception
	 */
	public function testUpdate(
		$data,
		$result,
		string $cleanTitle = null,
		string $cleanBody = null,
		string $exception = null
	)
	{
		// No need for full mock. Generate an empty Post for testing.
		$postMock = new Post();
		
		if ($result === self::NEW_POST) {
			$post = PostFactory::update($data, $postMock);
			
			self::assertTrue($post instanceof Post);
			
			self::assertEquals($cleanTitle, $post->getTitle());
			self::assertEquals($cleanBody, $post->getBody());
		}
		
		if ($result === self::EXCEPTION && $exception) {
			$this->expectException($exception);
			
			PostFactory::make($data);
		}
	}
	
	public
	function provideTestCases(): Generator
	{
		$title = 'the blog post title';
		
		$body =
			"This is the 150 character body. This is a default body with no special stuff in it. Append some crap at the end for checking of tag or js removal. AWESOME!!!! So get on with it my friend! This is the 150 character body. This is a default body with no special stuff in it. Append some crap at the end for checking of tag or js removal. AWESOME!!!! So get on with it my friend! This is the 150 character body. This is a default body with no special stuff in it. Append some crap at the end for checking of tag or js removal. AWESOME!!!! So get on with it my friend!";
		
		$javascript = '<script>alert("hi fucko!!!")</script><script src="PostFactoryTest.php"></script>';
		
		$forbiddenTags = '<iframe>asd</iframe><link rel="stylesheet" href="PostFactoryTest.php">';
		
		$onClicks = '<div onclick="alert(\'asd\')" onmouseenter="alert(\'asd\')" onmouseover="alert(\'asd\')" onmousemove="alert(\'asd\')">attrDiv</div>';
		
		yield 'correct data with title and body as string' => [
			json_encode(['title' => $title, 'body' => $body]),
			self::NEW_POST,
			$title,
			$body,
		];
		
		yield 'correct data with title and body as array' => [
			['title' => $title, 'body' => $body],
			self::NEW_POST,
			$title,
			$body,
		];
		
		yield 'correct data with no title but with body' => [
			['body' => $body],
			self::EXCEPTION,
			null,
			null,
			ArrayKeyNotSetException::class,
		];
		
		yield 'correct data with title but no body' => [
			['title' => $title],
			self::EXCEPTION,
			null,
			null,
			ArrayKeyNotSetException::class,
		];
		
		yield 'array with no title and no body' => [
			['category' => 'some category'],
			self::EXCEPTION,
			null,
			null,
			ArrayKeyNotSetException::class,
		];
		
		yield 'empty array' => [
			[],
			self::EXCEPTION,
			null,
			null,
			InvalidArgumentException::class,
		];
		
		yield 'empty string' => [
			'',
			self::EXCEPTION,
			null,
			null,
			InvalidArgumentException::class,
		];
		
		yield 'correct data with javascript in title' => [
			['title' => $title . $javascript, 'body' => $body],
			self::NEW_POST,
			$title,
			$body,
			null,
		];
		
		yield 'correct data with javascript in body' => [
			['title' => $title, 'body' => $body . $javascript],
			self::NEW_POST,
			$title,
			$body,
			null,
		];
		
		yield 'correct data with forbidden tags in title and in body' => [
			['title' => $title . $forbiddenTags, 'body' => $body . $forbiddenTags],
			self::NEW_POST,
			$title . 'asd',
			$body . 'asd',
			null,
		];
		
		yield 'correct data with onclick attributes in title and in body' => [
			['title' => $title . $forbiddenTags . $onClicks, 'body' => $body . $forbiddenTags . $onClicks],
			self::NEW_POST,
			$title . 'asd' . 'attrDiv',
			$body . 'asd' . '<div>attrDiv</div>',
			null,
		];
	}
}