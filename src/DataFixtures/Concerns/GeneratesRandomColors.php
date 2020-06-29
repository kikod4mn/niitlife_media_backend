<?php

declare(strict_types = 1);

namespace App\DataFixtures\Concerns;

trait GeneratesRandomColors
{
	/**
	 * @return string
	 */
	protected function randomHexColor(): string
	{
		$hexChars  = 'ABCDEF0123456789';
		$randomCol = '#';
		for ($i = 0; $i < 6; $i++) {
			$randomCol .= $hexChars[mt_rand(0, strlen($hexChars) - 1)];
		}
		
		return $randomCol;
	}
}