<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\Event\UserCreatedEvent;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordHashSubscriber implements EventSubscriberInterface
{
	/**
	 * @var UserPasswordEncoderInterface
	 */
	private UserPasswordEncoderInterface $passwordEncoder;
	
	/**
	 * PasswordHashSubscriber constructor.
	 * @param  UserPasswordEncoderInterface  $passwordEncoder
	 */
	public function __construct(UserPasswordEncoderInterface $passwordEncoder)
	{
		$this->passwordEncoder = $passwordEncoder;
	}
	
	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			UserCreatedEvent::class => ['hashPassword', 999],
		];
	}
	
	/**
	 * @param  UserCreatedEvent  $event
	 */
	public function hashPassword(UserCreatedEvent $event): void
	{
		/** @var User $user */
		$user = $event->getUser();
		
		if (! $user instanceof User) {
			
			return;
		}
		
		if (null !== $user->getPlainPassword() && null !== $user->getRetypedPlainPassword()) {
			
			$user->setPassword(
				$this->getPasswordEncoder()->encodePassword($user, $user->getPlainPassword())
			);
			
			$user->eraseCredentials();
		}
	}
	
	/**
	 * @return UserPasswordEncoderInterface
	 */
	public function getPasswordEncoder(): UserPasswordEncoderInterface
	{
		return $this->passwordEncoder;
	}
}