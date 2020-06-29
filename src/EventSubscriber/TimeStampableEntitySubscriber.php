<?php

declare(strict_types = 1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Contracts\TimeStampable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class TimeStampableEntitySubscriber implements EventSubscriberInterface
{
	/**
	 * @return array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			KernelEvents::VIEW => [
				['setCreationStamp', EventPriorities::PRE_WRITE],
				['setUpdatedStamp', EventPriorities::PRE_WRITE],
			],
		];
	}
	
	/**
	 * @param  ViewEvent  $event
	 */
	public function setCreationStamp(ViewEvent $event): void
	{
		$entity = $event->getControllerResult();
		$method = $event->getRequest()->getMethod();
		
		if (! $entity instanceof TimeStampable || Request::METHOD_POST !== $method) {
			
			return;
		}
		
		if ($entity->getCreatedAt() === null && $entity->hasTimestamps()) {
			
			$entity->setCreationTimestamps();
		}
	}
	
	/**
	 * @param  ViewEvent  $event
	 */
	public function setUpdatedStamp(ViewEvent $event): void
	{
		$entity = $event->getControllerResult();
		$method = $event->getRequest()->getMethod();
		
		if (! $entity instanceof TimeStampable || Request::METHOD_PUT !== $method) {
			
			return;
		}
		
		if ($entity->getUpdatedAt() === null && $entity->hasTimestamps()) {
			
			$entity->setUpdatedTimestamps();
		}
	}
}