<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\Contracts\Publishable;
use App\Entity\Image;
use App\Security\Concerns\ChecksPermissions;
use App\Security\Contracts\CheckablePermissions;
use App\Security\Contracts\VotableConstants;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class ImageVoter extends Voter implements VotableConstants, CheckablePermissions
{
	use ChecksPermissions;
	
	/**
	 * PostVoter constructor.
	 * @param  Security  $security
	 */
	public function __construct(Security $security)
	{
		$this->setSecurity($security);
		$this->setUser();
	}
	
	/**
	 * @param  string  $attribute
	 * @param  mixed   $subject
	 * @return bool|void
	 */
	public function supports(string $attribute, $subject): bool
	{
		return in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE])
			&& $subject instanceof Image;
	}
	
	/**
	 * @param  string          $attribute
	 * @param  mixed           $subject
	 * @param  TokenInterface  $token
	 * @return bool|void
	 */
	public function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
	{
		switch ($attribute) {
			case self::VIEW:
				if ($subject instanceof Publishable) {
					
					return $subject->isPublished();
				}
				
				return true;
			case self::CREATE:
			case self::EDIT:
			case self::DELETE:
				return $this->isUserAdmin();
		}
		
		throw new LogicException('This code should not run');
	}
}