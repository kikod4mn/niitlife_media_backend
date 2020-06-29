<?php

declare(strict_types = 1);

namespace App\Entity\Contracts;

interface Uuidable
{
	/**
	 * @return null|string
	 */
	public function getId(): ?string;
	
	/**
	 * Generate a uuid for the entity.
	 */
	public function generateUuid(): void;
}