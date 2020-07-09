<?php

declare(strict_types = 1);

namespace App\Service\ImageProcessing;

use App\Service\FileUploaderService;
use App\Support\Validate;
use Symfony\Component\HttpFoundation\File\File;

class ImageProcessingService
{
	const ADD_WATERMARK      = 'add_watermark';
	
	const THUMBNAIL_SIZE     = 'thumbnail_size';
	
	const GENERATE_THUMBNAIL = 'thumbnail';
	
	const GENERATE_HALF_SIZE = 'half_size';
	
	const SORT_BY_MONTH      = 'sort_by_month';
	
	const SORT_BY_YEAR       = 'sort_by_year';
	
	const SIZES              = 'sizes';
	
	private array  $defaultOptions = [];
	
	private array  $validOptions   = [];
	
	private array  $options        = [];
	
	private string $imageDir;
	
	/**
	 * @var File
	 */
	private File           $image;
	
	private array          $output      = [];
	
	private ?string        $newFilename = null;
	
	private ?string        $webPath     = null;
	
	private array          $sizes       = [];
	
	/**
	 * ImageProcessingService constructor.
	 * @param  string  $webPath
	 * @param  string  $imageDir
	 * @param  File    $image
	 * @param  array   $options
	 */
	public function __construct(string $webPath, string $imageDir, File $image, array $options = [])
	{
		$this->initDefault();
		
		$this->assignOptions($options);
		
		$this->webPath  = rtrim($webPath, '/');
		$this->imageDir = rtrim($imageDir, '/\\');
		$this->image    = $image;
	}
	
	/**
	 * Get the array of available options to set on ImageProcessing.
	 * @return array
	 */
	public function getValidOptions(): array
	{
		return $this->validOptions;
	}
	
	/**
	 * Get the currently enabled and set options.
	 * @return array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}
	
	/**
	 * View default options.
	 * @return array
	 */
	public function getDefaultOptions(): array
	{
		return $this->defaultOptions;
	}
	
	/**
	 * Return the new filename, without extension.
	 * @return null|string
	 */
	public function getNewFilename(): ?string
	{
		return $this->newFilename;
	}
	
	public function getImageDir(): ?string
	{
		return $this->imageDir;
	}
	
	/**
	 * Return the base domain and path to the image.
	 * @return null|string
	 */
	public function getWebPath(): ?string
	{
		return $this->webPath;
	}
	
	/**
	 * @return $this|ProcessedImage
	 */
	public function output(): ProcessedImage
	{
		$filePath = $this->setOriginal();
		
		if (count($this->sizes) > 0) {
			
			foreach ($this->sizes as $key => $value) {
				
				$filename = $this->newSubImage($filePath, $value, $this->getFinalFilenameWithExtension($key));
				
				$this->output[$key] = $this->getWebPath() . $filename;
			}
		}
		
		return $this->generateProcessedImage();
	}
	
	/**
	 * Generate a new sub image from the current image.
	 * @param  string       $filePath
	 * @param  null|int     $size
	 * @param  string       $filename
	 * @param  null|string  $key
	 * @return string
	 */
	private function newSubImage(string $filePath, ?int $size, string $filename, ?string $key = null): string
	{
		return (new ImageResizer($filePath))->resize($size, $filename, $key);
	}
	
	/**
	 * Generate a new object of a processed image with all properties from the output of this class.
	 * ProcessedImage uses magic __get() function to make sure there are no exceptions thrown with unknown properties.
	 * Returns a value if property exists, null otherwise.
	 * @return ProcessedImage
	 */
	private function generateProcessedImage(): ProcessedImage
	{
		return new ProcessedImage($this->output);
	}
	
	/**
	 * Set the original in the output array.
	 */
	private function setOriginal(): string
	{
		[
			$fileWebPath,
			$safeFilename,
			$absoluteFilePath,
			$generalWebPath,
		] = (new FileUploaderService($this->getImageDir(), $this->getWebPath()))
			->move($this->image);
		
		$this->output['original'] = $fileWebPath;
		
		// Replace the base web path with the one from file upload service in case months and years are used in the file path.
		$this->webPath = $generalWebPath;
		
		// Set the file name that is now saved to our system.
		$this->newFilename = $safeFilename;
		
		return $absoluteFilePath;
	}
	
	//	/**
	//	 * Move the original to the assigned directory with its new filename.
	//	 * Checks to see if the file with the same name already exists and if it does, will a new filename or append to the old name..
	//	 * @return string
	//	 * @throws Exception
	//	 */
	//	private function moveOriginal(): string
	//	{
	//		// Check if a file with the same name exists, if it does, generate a new filename.
	//		if (file_exists($this->imageDir . '/' . $this->getFinalFilenameWithExtension())) {
	//
	//			$this->fileExists = true;
	//
	//			$this->assignFilename();
	//
	//			return $this->moveOriginal();
	//		}
	//
	//		try {
	//
	//			$this->image->move($this->imageDir, $this->getFinalFilenameWithExtension());
	//
	//			return $this->getWebPath() . '/' . $this->getFinalFilenameWithExtension();
	//		} catch (Throwable $e) {
	//
	//			throw new $e;
	//		}
	//	}
	
	/**
	 * Get the formatted filename with the extension and possible addition to the name.
	 * @param  string  $add
	 * @return string
	 */
	private function getFinalFilenameWithExtension(string $add = ''): string
	{
		return FileUploaderService::addToFilename($this->getNewFilename(), $add) . '.' . $this->image->guessExtension();
	}
	
	//	/**
	//	 * Generate a new filename or set the old filename.
	//	 * Generates a random filename of numbers and letters to avoid conflicting names.
	//	 * Filename is without extension.
	//	 * Adds 4 characters to the default name to avoid conflicts if file already exists on the server.
	//	 * @throws Exception
	//	 */
	//	private function assignFilename(): void
	//	{
	//		if (true === $this->getOption(self::GENERATE_NEW_FILENAME)) {
	//
	//			$this->newFilename = Str::random(64);
	//
	//		} else if (false === $this->getOption(self::GENERATE_NEW_FILENAME) && $this->fileExists) {
	//
	//			$this->newFilename = $this->image->getFilename() . Str::random(4);
	//
	//			$this->fileExists = false;
	//		} else {
	//
	//			$this->newFilename = $this->image->getFilename();
	//		}
	//
	//	}
	
	private function initDefault(): void
	{
		$this->validOptions = [
			self::GENERATE_THUMBNAIL, self::GENERATE_HALF_SIZE, self::SORT_BY_MONTH,
			self::SORT_BY_YEAR, self::THUMBNAIL_SIZE, self::ADD_WATERMARK, self::SIZES,
		];
		
		$this->defaultOptions = [
			self::ADD_WATERMARK      => true,
			self::SIZES              => [],
			self::GENERATE_THUMBNAIL => true,
			self::GENERATE_HALF_SIZE => true,
			self::SORT_BY_MONTH      => true,
			self::SORT_BY_YEAR       => true,
			self::THUMBNAIL_SIZE     => 150,
		];
	}
	
	private function assignOptions(array $options): void
	{
		foreach ($this->getValidOptions() as $option) {
			
			if (array_key_exists($option, $options) && ! Validate::blank($options[$option])) {
				
				$this->setOption($option, $options[$option]);
			} else {
				
				$this->setOption($option, $this->getDefaultOption($option));
			}
		}
		
		if (true === $this->getOption(self::GENERATE_THUMBNAIL)) {
			$this->sizes['thumbnail'] = $this->getOption(self::THUMBNAIL_SIZE);
		}
		
		if (true === $this->getOption(self::GENERATE_HALF_SIZE)) {
			$this->sizes['half_size'] = null;
		}
		
		$sizes = $this->getOption(self::SIZES);
		
		if (count($sizes) > 0) {
			
			foreach ($sizes as $key => $value) {
				
				if (! $key === 'thumbnail' && ! $key === 'half_size' && is_int($value)) {
					
					$this->sizes[$key] = $value;
				}
			}
		}
		
		//		$this->assignFilename();
		
		//		if (true === $this->getOption(self::SORT_BY_YEAR)) {
		//
		//			$this->addToImageDir(date('Y'));
		//		}
		//
		//		if (true === $this->getOption(self::SORT_BY_MONTH)) {
		//
		//			$this->addToImageDir(strtolower(date('F')));
		//		}
	}
	//
	//	private function addToImageDir(string $add): void
	//	{
	//		$this->imageDir = rtrim($this->imageDir, '/\\') . '/' . $add . '/';
	//	}
	
	private function setOption(string $key, $value): void
	{
		$this->options[$key] = $value;
	}
	
	private function getOption(string $key)
	{
		return isset($this->options[$key]) ? $this->options[$key] : null;
	}
	
	private function getDefaultOption(string $key)
	{
		if (array_key_exists($key, $this->getDefaultOptions())) {
			
			return $this->getDefaultOptions()[$key];
		}
		
		return null;
	}
}