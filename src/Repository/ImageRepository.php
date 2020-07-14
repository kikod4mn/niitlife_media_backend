<?php

namespace App\Repository;

use App\Entity\Image;
use App\Entity\Tag;
use App\Repository\Contracts\FindForTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Image|null find($id, $lockMode = null, $lockVersion = null)
 * @method Image|null findOneBy(array $criteria, array $orderBy = null)
 * @method Image[]    findAll()
 * @method Image[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImageRepository extends ServiceEntityRepository
{
	use FindForTag;
	
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, Image::class);
	}
	
	public function getForTag(Tag $tag, int $page, int $perPage)
	{
		$count = $this->countForTag($tag, 'i');
		
		$lastPage = ceil($count / $perPage);
		
		if (! $this->overLastPage($page, $perPage, $count)) {
			
			$results = $this->createQueryBuilder('i')
			                ->where(':tag MEMBER OF i.tags')
			                ->andWhere('i.publishedAt IS NOT NULL')
			                ->andWhere('i.trashedAt IS NULL')
			                ->setParameter('tag', $tag)
			                ->setFirstResult($this->getOffset($perPage, $page))
			                ->setMaxResults($perPage)
			                ->orderBy('i.createdAt', 'DESC')
			                ->getQuery()
			                ->getResult()
			;
			
			return [$results, $lastPage];
		}
		
		return [[], $lastPage];
	}
}
