<?php

declare(strict_types = 1);

namespace App\Tests\Support;

use App\Support\Validate;
use Generator;
use PHPUnit\Framework\TestCase;
use stdClass;

class ValidateTest extends TestCase
{
	/**
	 * @dataProvider provideBlanks
	 * @param        $var
	 * @param  bool  $result
	 */
	public function testBlank($var, bool $result)
	{
		$this->assertEquals($result, Validate::blank($var));
	}
	
	/**
	 * @return Generator
	 */
	public function provideBlanks(): Generator
	{
		yield 'blank string' => ['', true];
		yield 'non blank string' => ['non blank string', false];
		yield 'empty array' => [[], true];
		yield 'boolean false' => [false, false];
		yield 'null value' => [null, true];
		yield 'empty object' => [(object) [], true];
		yield 'not empty object' => [(object) ['id' => 1], false];
	}
}