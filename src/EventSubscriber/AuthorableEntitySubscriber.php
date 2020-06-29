<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Contracts\Authorable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
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
			KernelEvents::VIEW => ['setAuthor', EventPriorities::PRE_WRITE],
		];
	}
	
	public function setAuthor(ViewEvent $event): void
	{
		$entity = $event->getControllerResult();
		$method = $event->getRequest()->getMethod();
		
		if (! $entity instanceof Authorable || Request::METHOD_POST !== $method) {
			return;
		}
		
		$entity->setAuthor($this->getSecurity()->getUser());
	}
	
	/**
	 * @return Security
	 */
	public function getSecurity(): Security
	{
		return $this->security;
	}
}