<?php

declare(strict_types = 1);

namespace App\Controller\Post;

use App\Security\Voter\PostVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class ListController extends AbstractController
{
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var PaginatorInterface
	 */
	private PaginatorInterface $paginator;
	
	/**
	 * @var SerializerInterface
	 */
	private SerializerInterface $serializer;
	
	/**
	 * ListController constructor.
	 * @param  EntityManagerInterface  $entityManager
	 * @param  PaginatorInterface      $paginator
	 * @param  SerializerInterface     $serializer
	 */
	public function __construct(
		EntityManagerInterface $entityManager,
		PaginatorInterface $paginator,
		SerializerInterface $serializer
	)
	{
		$this->entityManager = $entityManager;
		$this->paginator     = $paginator;
		$this->serializer    = $serializer;
	}
	
	public function __invoke(Request $request, int $page): JsonResponse
	{
		$qb = $this->getQueryBuilder()
		           ->select('p')
		           ->from('App\Entity\Post', 'p')
		           ->where('p.publishedAt IS NOT NULL')
		           ->andWhere('p.trashedAt IS NULL')
		           ->getQuery()
		;
		
		$limit = $request->get('limit', 10);
		
		$pagination = $this->getPaginator()->paginate($qb, $page, $limit);
		
		$currentPage = $pagination->getCurrentPageNumber();
		$lastPage    = ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage());
		
		$posts = [];
		
		foreach ($pagination->getItems() as $post) {
			
			if ($this->isGranted(PostVoter::VIEW, $post)) {
				
				$posts[] = $this->getSerializer()->normalize(
					$post,
					'json',
					['groups' => ['post:list']]
				)
				;
			}
		}
		
		return $this->json(
			[
				'posts'       => $posts,
				'currentPage' => $currentPage,
				'lastPage'    => $lastPage,
			]
		);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getQueryBuilder(): QueryBuilder
	{
		return $this->getEntityManager()->createQueryBuilder();
	}
	
	public function getPaginator(): PaginatorInterface
	{
		return $this->paginator;
	}
	
	public function getSerializer(): SerializerInterface
	{
		return $this->serializer;
	}
}