<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\AbstractEntity\AbstractEntity;
use App\Repository\UserProfileRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource(
 *     itemOperations={"GET"},
 *
 *     collectionOperations={"GET"}
 * )
 * @ORM\Entity(repositoryClass=UserProfileRepository::class)
 */
class UserProfile extends AbstractEntity
{
	/**
	 * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="userProfile")
	 * @var null|User
	 */
	protected ?User $user = null;
	
	/**
	 * @ORM\Column(type="text", nullable=true)
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
