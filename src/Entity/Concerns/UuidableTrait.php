<?php

declare(strict_types = 1);

namespace App\Entity\Concerns;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Serializer\Annotation\Groups;

trait UuidableTrait
{
	/**
	 * @return null|string
	 * NOTE : Symfony does not like strict types, attempt to cast the Uuid to a string before returning.
	 */
	public function getId(): ?string
	{
		return $this->id;
	}
	
	/**
	 * Generate a uuid for the entity.
	 */
	public function generateUuid(): void
	{
		$this->id = Uuid::uuid4()->toString();
	}
}