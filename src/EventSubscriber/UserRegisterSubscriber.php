<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\Event\UserCreatedEvent;
use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserRegisterSubscriber implements EventSubscriberInterface
{
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}
	
	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			UserCreatedEvent::class => ['createProfile', 998],
		];
	}
	
	/**
	 * @param  UserCreatedEvent  $event
	 */
	public function createProfile(UserCreatedEvent $event): void
	{
		/** @var User $user */
		$user = $event->getUser();
		
		if (! $user instanceof User) {
			
			return;
		}
		
		$profile = new UserProfile();
		$profile->setUser($user);
		
		$this->getEntityManager()->persist($profile);
	}
	
	/**
	 * @return EntityManagerInterface
	 */
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
}