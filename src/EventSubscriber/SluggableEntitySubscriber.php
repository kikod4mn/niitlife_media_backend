<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Contracts\Sluggable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SluggableEntitySubscriber implements EventSubscriberInterface
{
	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::VIEW => ['setSlug', EventPriorities::PRE_WRITE],
		];
	}
	
	/**
	 * @param  ViewEvent  $event
	 */
	public function setSlug(ViewEvent $event): void
	{
		$entity = $event->getControllerResult();
		$method = $event->getRequest()->getMethod();
		
		if (! $entity instanceof Sluggable || Request::METHOD_POST !== $method) {
			
			return;
		}
		
		if ($entity->getSlug() === null) {
			
			$entity->setSlug();
		}
	}
}