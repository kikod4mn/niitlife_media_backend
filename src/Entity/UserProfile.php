<?php

namespace App\Entity;

use App\Entity\AbstractEntity\AbstractEntity;
use Ramsey\Uuid\UuidInterface;

class UserProfile extends AbstractEntity
{
	/**
	 * @var null|UuidInterface
	 */
	protected ?UuidInterface $id = null;
	
	/**
	 * @var null|User
	 */
	protected ?User $user = null;
	
	/**
	 * @var null|string
	 */
	protected ?string $avatar = null;
	
	/**
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
		];
	}
	
	/**
	 * @return null|User
	 */
	public function getUser(): ?User
	{
		return $this->user;
	}
	
	/**
	 * @param  User  $user
	 * @return $this
	 */
	public function setUser(User $user): self
	{
		$this->user = $user;
		
		return $this;
	}
	
	/**
	 * @return null|string
	 */
	public function getAvatar(): ?string
	{
		return $this->avatar;
	}
	
	/**
	 * @param  null|string  $avatar
	 * @return $this|UserProfile
	 */
	public function setAvatar(?string $avatar): UserProfile
	{
		$this->avatar = $avatar;
		
		return $this;
	}
}
