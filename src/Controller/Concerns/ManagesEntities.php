<?php

declare(strict_types = 1);

namespace App\Controller\Concerns;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

trait ManagesEntities
{
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @return EntityManagerInterface
	 */
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getQueryBuilder(): QueryBuilder
	{
		return $this->getEntityManager()->createQueryBuilder();
	}
}