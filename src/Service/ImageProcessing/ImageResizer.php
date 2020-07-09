<?php

declare(strict_types = 1);

namespace App\Service\ImageProcessing;

use Exception;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

class ImageResizer
{
	const IMAGE_HANDLERS = [
		IMAGETYPE_JPEG => [
			'load'    => 'imagecreatefromjpg',
			'save'    => 'imagejpeg',
			'quality' => 100,
		],
		IMAGETYPE_PNG  => [
			'load'    => 'imagecreatefrompng',
			'save'    => 'imagepng',
			'quality' => 0,
		],
	];
	
	/**
	 * @var File
	 */
	private File $image;
	
	public function __construct(string $image)
	{
		$this->image = new File($image, true);
	}
	
	public function resize(?int $size, string $newName, ?string $key): string
	{
		// If null is passed in as a size, assume half quality image is the one generated.
		// Set minimum jpg quality and halve the image original size, but no larger than 450px.
		if (null === $size) {
			
			$currentImgSize = getimagesize($this->image->getRealPath());
			
			$newSize = $currentImgSize > 450 ? 450 : $currentImgSize;
			
			$lowQual = true;
			
		} else {
			
			$newSize = $size;
			
			$lowQual = false;
		}
		
		// If null is passed for key, use the size itself for the array key value.
		if (null === $key) {
			
			$key = (string) $size;
		}
		
		$type = exif_imagetype($this->image->getRealPath());
		
		if (false === $type || ! array_key_exists($type, self::IMAGE_HANDLERS)) {
			
			throw new FileException('File not supported');
		}
		
		$resource = call_user_func(self::IMAGE_HANDLERS[$type]['load'], $this->image->getRealPath());
		
		if (! $resource) {
			
			throw new Exception();
		}
		
		$width  = imagesx($resource);
		$height = imagesy($resource);
		$ratio  = $width / $height;
		
		if ($width > $height) {
			$targetHeight = floor($size / $ratio);
			$targetWidth  = $size;
		} else {
			$targetHeight = $size;
			$targetWidth  = floor($size * $ratio);
		}
		
		$resized = imagecreatetruecolor($targetWidth, $targetHeight);
		
		if ($type === IMAGETYPE_PNG) {
			imagecolortransparent(
				$resized,
				imagecolorallocate($resized, 0, 0, 0)
			);
			
			imagealphablending($resized, false);
			imagesavealpha($resized, true);
		}
		
		imagecopyresampled(
			$resized,
			$resource,
			0, 0, 0, 0,
			$targetWidth, $targetHeight,
			$width, $height
		);
		
		call_user_func(
			self::IMAGE_HANDLERS[$type]['save'],
			$resized,
			rtrim($this->image->getPath(), '/\\') . '/' . $newName,
			$lowQual ? 0 : self::IMAGE_HANDLERS[$type]['quality']
		);
		
		return $newName;
	}
}