<?php

declare(strict_types = 1);

namespace App\Security\Concerns;

use App\Entity\Contracts\Authorable;
use App\Entity\User;
use App\Entity\UserProfile;
use App\Security\Contracts\CheckablePermissions;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Helper trait for CheckablePermissions Interface.
 */
trait ChecksPermissions
{
	/**
	 * @var null|User|UserInterface|string
	 */
	private $user = null;
	
	/**
	 * @var null|Security
	 */
	private ?Security $security = null;
	
	/**
	 * @return null|User|UserInterface
	 */
	public function getUser(): ?UserInterface
	{
		return $this->user;
	}
	
	/**
	 * @return $this|CheckablePermissions
	 */
	public function setUser(): self
	{
		$this->user = $this->getSecurity()->getUser() ?? $this->getSecurity()->getToken()->getUser();
		
		return $this;
	}
	
	/**
	 * @return null|Security
	 */
	public function getSecurity(): ?Security
	{
		return $this->security;
	}
	
	/**
	 * @param  Security  $security
	 * @return $this|CheckablePermissions
	 */
	public function setSecurity(Security $security): self
	{
		$this->security = $security;
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function hasUser(): bool
	{
		return ! is_null($this->getUser()) && $this->getUser() instanceof User;
	}
	
	/**
	 * @return bool
	 */
	public function hasSecurity(): bool
	{
		return ! is_null($this->getSecurity()) && $this->getSecurity() instanceof Security;
	}
	
	/**
	 * @return bool
	 */
	public function isUserAdmin(): bool
	{
		return $this->getSecurity()->isGranted(User::ROLE_ADMINISTRATOR);
	}
	
	/**
	 * @return bool
	 */
	public function isUserAnon(): bool
	{
		return $this->getSecurity()->isGranted('IS_AUTHENTICATED_ANONYMOUSLY');
	}
	
	public function isUserFullyAuth()
	{
		return $this->getSecurity()->isGranted('IS_AUTHENTICATED_FULLY');
	}
	
	/**
	 * @param  Authorable|User|UserProfile|mixed  $subject
	 * @return bool
	 */
	public function isOwner($subject): bool
	{
		if (! $this->hasUser()) {
			
			return false;
		}
		
		return $this->ownerCheckMethod($subject);
	}
	
	/**
	 * @param  Authorable|User|UserProfile|mixed  $subject
	 * @return bool
	 */
	protected function ownerCheckMethod($subject): bool
	{
		switch ($subject) {
			case $subject instanceof Authorable:
				return $this->getUser()->getId() === $subject->getAuthor()->getId();
			case $subject instanceof User:
				return $this->getUser()->getId() === $subject->getId();
			case $subject instanceof UserProfile:
				return $this->getUser()->getId() === $subject->getUser()->getId();
			default:
				return false;
		}
	}
}