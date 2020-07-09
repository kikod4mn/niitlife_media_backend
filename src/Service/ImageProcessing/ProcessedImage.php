<?php

declare(strict_types = 1);

namespace App\Service\ImageProcessing;

class ProcessedImage
{
	/**
	 * ProcessedImage constructor.
	 * @param  array  $sizes
	 */
	public function __construct(array $sizes)
	{
		$this->setSizes($sizes);
	}
	
	private function setSizes(array $sizes)
	{
		foreach ($sizes as $key => $value) {
			
			$this->$key = $value;
		}
	}
	
	public function __get(string $prop)
	{
		if (property_exists($this, $prop)) {
			
			return $this->$prop;
		}
		
		return null;
	}
}