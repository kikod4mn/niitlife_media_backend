<?php

declare(strict_types = 1);

namespace App\Security\Voter;

use App\Entity\ImageCategory;
use App\Security\Concerns\ChecksPermissions;
use App\Security\Contracts\CheckablePermissions;
use App\Security\Contracts\VotableConstants;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class ImageCategoryVoter extends Voter implements VotableConstants, CheckablePermissions
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
			&& $subject instanceof ImageCategory;
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
				return $subject->isPublished();
			case self::CREATE:
			case self::EDIT:
			case self::PUBLISH:
			case self::TRASH:
			case self::RESTORE:
			case self::DELETE:
				return $this->isUserAdmin();
		}
		
		throw new LogicException('This code should not run');
	}
}