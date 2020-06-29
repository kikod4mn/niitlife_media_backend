<?php

declare(strict_types = 1);

namespace App\Security;

use Exception;
use function random_int;
use function strlen;

class TokenGenerator
{
	/**
	 * @var string
	 */
	private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	
	/**
	 * @param  int  $length
	 * @return string
	 * @throws Exception
	 */
	public function generateToken(int $length = 64): string
	{
		$maxNumber = strlen(self::ALPHABET);
		$token     = '';
		
		for ($i = 0; $i < $length; $i++) {
			$token .= self::ALPHABET[random_int(0, $maxNumber - 1)];
		}
		
		return $token;
	}
	
	/**
	 * @param  int  $length
	 * @return string
	 * @throws Exception
	 */
	public function generateNumericCode(int $length = 10): string
	{
		$code = '';
		
		for ($i = 0; $i < $length; $i++) {
			$code .= random_int(0, 9);
		}
		
		return $code;
	}
}