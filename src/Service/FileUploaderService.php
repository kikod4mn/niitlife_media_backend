<?php

declare(strict_types = 1);

namespace App\Service;

use App\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

class FileUploaderService
{
	private ?string          $targetDir          = null;
	
	private bool             $separatedByMonth   = true;
	
	private bool             $separatedByYear    = true;
	
	private bool             $createSafeFilename = false;
	
	private ?string          $dbDir              = null;
	
	private ?string          $safeFilename       = null;
	
	private ?string          $originalFilename   = null;
	
	public function __construct(string $targetDir, string $dbDir)
	{
		$this->targetDir = self::addToDir($targetDir, '');
		$this->dbDir     = self::addToDir($dbDir, '');
	}
	
	public function move(File $file, string $add = ''): array
	{
		if (true === $this->getSeparatedByYear()) {
			
			$this->addYear();
		}
		
		if (true === $this->getSeparatedByMonth()) {
			
			$this->addMonth();
		}
		
		if (true === $this->getCreateSafeFilename()) {
			
			$this->createSafeFilename($file);
		}
		
		if (true === $this->doesFileExist($this->getTargetDir() . $file->getClientOriginalName() . '.' . $file->guessExtension())) {
			
			throw new FileException('File already exists with the same name in the directory.');
		}
		
		try {
			$newName = self::addToFilename($this->getSafeFilename(), $add);
			
			$pos = mb_strrpos($newName, '.');
			
			if (! $pos) {
				
				$newName .= '.' . $file->guessExtension();
			}
			
			$file->move($this->getTargetDir(), $newName);
		} catch (Throwable $e) {
			
			throw new $e;
		}
		
		return [
			'fileWebPath'      => $this->getDbDir() . $this->getSafeFilename() . '.' . $file->guessExtension(),
			'safeFilename'     => $this->getSafeFilename(),
			'absoluteFilePath' => $this->getTargetDir() . $this->getSafeFilename() . '.' . $file->guessExtension(),
			'generalWebPath'   => $this->getDbDir(),
		
		];
	}
	
	public function createSafeFilename(File $file): string
	{
		$safeName = Str::random(64);
		
		if ($this->doesFileExist($this->getTargetDir() . $safeName . '.' . $file->guessExtension())) {
			
			return $this->createSafeFilename($file);
		}
		
		$this->safeFilename = $safeName;
		
		return $this->safeFilename;
	}
	
	public static function doesFileExist(string $fullpath): bool
	{
		return file_exists($fullpath);
	}
	
	public function getCreateSafeFilename(): bool
	{
		return $this->createSafeFilename;
	}
	
	public function getSeparatedByMonth(): bool
	{
		return $this->separatedByMonth;
	}
	
	public function getSeparatedByYear(): bool
	{
		return $this->separatedByYear;
	}
	
	public function getOriginalFilename(): ?string
	{
		return $this->originalFilename;
	}
	
	public function getSafeFilename(): ?string
	{
		return $this->safeFilename;
	}
	
	public function getTargetDir(): ?string
	{
		return $this->targetDir;
	}
	
	public function getDbDir(): string
	{
		return $this->dbDir;
	}
	
	public function addToTargetDir(string $add): self
	{
		$this->targetDir = self::addToDir($this->targetDir, $add);
		$this->dbDir     = self::addToDir($this->dbDir, $add);
		
		return $this;
	}
	
	public function addYear(): self
	{
		$this->dbDir     = self::addToDir($this->dbDir, date('Y'));
		$this->targetDir = self::addToDir($this->targetDir, date('Y'));
		
		return $this;
	}
	
	public function addMonth(): self
	{
		$this->dbDir     = self::addToDir($this->dbDir, strtolower(date('F')));
		$this->targetDir = self::addToDir($this->targetDir, strtolower(date('F')));
		
		return $this;
	}
	
	public static function addToFilename(string $filename, string $add): string
	{
		if ($add === '') {
			
			return $filename;
		}
		
		$pos = mb_strrpos($filename, '.');
		
		if (false === $pos) {
			
			return $filename . '_' . $add;
		}
		
		$beforeDot = mb_substr($filename, 0, $pos, 'UTF-8');
		$afterDot  = mb_substr($filename, $pos, null, 'UTF-8');
		
		return "{$beforeDot}_{$add}.{$afterDot}";
	}
	
	private static function addToDir(string $dir, string $add): string
	{
		if ($add === '') {
			
			return rtrim($dir, '/\\') . '/';
		}
		
		return rtrim($dir, '/\\') . '/' . rtrim(ltrim($add, '/\\'), '/\\') . '/';
	}
}