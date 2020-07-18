<?php

declare(strict_types = 1);

namespace App\Tests\Contracts;

use App\Support\Validate;

trait FindsFromRawOrValid
{
	/**
	 * @param  string  $key
	 * @param          $raw
	 * @return mixed
	 */
	protected function getValueFromRaw(string $key, $raw)
	{
		if (! is_array($raw)) {
			
			$raw = (array) (is_string($raw) ? json_decode($raw) : $raw);
		}
		
		// If the key does not exist inside raw or is blank, return the validCreateData key.
		if (! array_key_exists($key, $raw) || Validate::blank($raw[$key])) {
			
			return $this->validCreateData[$key];
		}
		
		if (str_contains($raw[$key], $this->validUpdateData[$key])) {
			
			return $this->validUpdateData[$key];
		}
		
		return $raw[$key];
	}
}