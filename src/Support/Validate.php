<?php

declare(strict_types = 1);

namespace App\Support;

class Validate
{
	/**
	 * Blank values are considered anything but a non empty array, non empty string and any boolean.
	 * @param $var
	 * @return bool
	 */
	public static function blank($var): bool
	{
		if (is_string($var) && $var !== '') {
			
			return false;
		}
		
		if (is_array($var) && $var !== []) {
			
			return false;
		}
		
		if (is_bool($var)) {
			
			return false;
		}
		
		if (is_object($var)) {
			
			return count(get_object_vars($var)) > 0 ? false : true;
		}
		
		return true;
	}
}