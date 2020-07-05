<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use App\Entity\Contracts\Authorable;
use App\Entity\Event\AuthorableCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class AuthorableEntitySubscriber implements EventSubscriberInterface
{
	/**
	 * @var Security
	 */
	private Security $security;
	
	/**
	 * AuthorableEntitySubscriber constructor.
	 * @param  Security  $security
	 */
	public function __construct(Security $security)
	{
		$this->security = $security;
	}
	
	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			AuthorableCreatedEvent::class => ['setAuthor', 999],
		];
	}
	
	public function setAuthor(AuthorableCreatedEvent $event): void
	{
		$entity = $event->getEntity();
		
		if ($entity->getAuthor() === null && $entity instanceof Authorable) {
			
			$entity->setAuthor($this->getSecurity()->getUser());
		}
	}
	
	/**
	 * @return Security
	 */
	public function getSecurity(): Security
	{
		return $this->security;
	}
}