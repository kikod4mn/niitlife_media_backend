<?php

declare(strict_types = 1);

namespace App\Repository\Concerns;

use App\Entity\Contracts\Publishable;
use App\Entity\Contracts\Trashable;
use App\Entity\Tag;

trait FindForTag
{
	/**
	 * @var null|string
	 */
	private ?string $alias = null;
	
	/**
	 * @param  int       $page
	 * @param  int       $perPage
	 * @param  null|int  $count
	 * @return bool
	 */
	public function overLastPage(int $page, int $perPage, ?int $count): bool
	{
		return ! ($page <= ceil($count / $perPage));
	}
	
	/**
	 * @param  Tag          $tag
	 * @param  null|string  $alias
	 * @return mixed
	 */
	protected function countForTag(Tag $tag, ?string $alias = null)
	{
		$alias = $alias ?? $this->getAlias();
		
		$qb = $this->createQueryBuilder($alias)
		           ->select('COUNT(' . $alias . '.id)')
		           ->where(':tag MEMBER OF ' . $alias . '.tags')
		           ->setParameter('tag', $tag)
		;
		
		if ($this->isPublishable()) {
			
			$qb->andWhere($alias . '.publishedAt IS NOT NULL');
		}
		
		if ($this->isTrashable()) {
			
			$qb->andWhere($alias . '.trashedAt IS NULL');
		}
		
		return $qb->getQuery()->getSingleScalarResult();
	}
	
	/**
	 * @param  int  $perPage
	 * @param  int  $page
	 * @return int
	 */
	protected function getOffset(int $perPage, int $page): int
	{
		return $perPage * $page - $perPage;
	}
	
	/**
	 * @return bool
	 */
	protected function isPublishable(): bool
	{
		return is_a($this->getEntityName(), Publishable::class, true);
	}
	
	/**
	 * @return bool
	 */
	protected function isTrashable(): bool
	{
		return is_a($this->getEntityName(), Trashable::class, true);
	}
	
	/**
	 * @return string
	 */
	protected function getAlias(): string
	{
		if (null === $this->getAlias()) {
			
			$this->alias = $this->createRootAlias();
		}
		
		return $this->alias;
	}
	
	/**
	 * @return string
	 */
	protected function createRootAlias(): string
	{
		$pos = mb_strrpos('\\', $this->getEntityName());
		
		if (! $pos) {
			
			return mb_strtolower(
				mb_substr(
					$this->getEntityName(), 0, 1
				)
			);
		}
		
		return mb_strtolower(
			mb_substr(
				$this->getEntityName(), $pos, 1
			)
		);
	}
}