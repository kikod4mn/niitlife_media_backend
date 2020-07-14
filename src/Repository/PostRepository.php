<?php

namespace App\Repository;

use App\Entity\Contracts\Publishable;
use App\Entity\Contracts\Trashable;
use App\Entity\Post;
use App\Entity\Tag;
use App\Repository\Contracts\FindForTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
	use FindForTag;
	
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Post::class);
	}
	
	public function getForTag(Tag $tag, int $page, int $perPage)
	{
		$count = $this->countForTag($tag, 'p');
		
		$lastPage = ceil($count / $perPage);
		
		if (! $this->overLastPage($page, $perPage, $count)) {
			
			$results = $this->createQueryBuilder('p')
			                ->where(':tag MEMBER OF p.tags')
			                ->andWhere('p.publishedAt IS NOT NULL')
			                ->andWhere('p.trashedAt IS NULL')
			                ->setParameter('tag', $tag)
			                ->setFirstResult($this->getOffset($perPage, $page))
			                ->setMaxResults($perPage)
			                ->orderBy('p.createdAt', 'DESC')
			                ->getQuery()
			                ->getResult()
			;
			
			return [$results, $lastPage];
		}
		
		return [[], $lastPage];
	}
}
