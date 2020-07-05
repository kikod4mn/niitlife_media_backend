<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

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
			KernelEvents::VIEW => ['hashPassword', 999],
		];
	}
	
	/**
	 * @param  ViewEvent  $event
	 */
	public function hashPassword(ViewEvent $event): void
	{
		$user   = $event->getControllerResult();
		$method = $event->getRequest()->getMethod();
		
		if (! $user instanceof User || ! in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])) {
			return;
		}
		
		if ($user->getPlainPassword() !== null) {
			
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