<?php

declare(strict_types = 1);

namespace App\Service\ImageProcessing;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

class Watermarker
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
	
	/**
	 * @var File
	 */
	private File $watermark;
	
	public function __construct(File $image, ?File $watermark)
	{
		$this->image = $image;
		
		if (null === $watermark) {
			
			new File(rtrim(getenv('projectDir'), '/\\') . '/public/images/watermark/watermark.png', true);
			
		} else {
			
			$this->watermark = $watermark;
		}
	}
	
	public function mark()
	{
		$type = exif_imagetype($this->image->getRealPath());
		
		if (false === $type || ! array_key_exists($type, self::IMAGE_HANDLERS)) {
			
			throw new FileException('File not supported');
		}
		
		$resource  = call_user_func(self::IMAGE_HANDLERS[$type]['load'], $this->image->getRealPath());
		$watermark = call_user_func(self::IMAGE_HANDLERS[$type]['load'], $this->watermark->getRealPath());
		
		$markWidth  = imagesx($watermark);
		$markHeight = imagesy($watermark);
		$h          = imagesx($resource);
		$w          = imagesy($resource);
		
		$marked = imagecopyresampled(
			$resource,
			$watermark,
			0.95 * $h - $markWidth,
			0.95 * $w - $markHeight,
			0,
			0,
			$markWidth,
			$markHeight,
			$w,
			$h
		);
		
		call_user_func(
			self::IMAGE_HANDLERS[$type]['save'],
			$marked,
			$this->image->getRealPath(),
			self::IMAGE_HANDLERS[$type]['quality']
		);
	}
}