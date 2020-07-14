<?php

declare(strict_types = 1);

namespace App\Controller\PostCategory;

use App\Repository\PostCategoryRepository;
use App\Security\Voter\PostVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class FindBySlugController extends AbstractController
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
	 * @var PostCategoryRepository
	 */
	private PostCategoryRepository $categoryRepository;
	
	public function __construct(
		PaginatorInterface $paginator,
		EntityManagerInterface $entityManager,
		SerializerInterface $serializer,
		PostCategoryRepository $categoryRepository
	)
	{
		$this->entityManager      = $entityManager;
		$this->paginator          = $paginator;
		$this->serializer         = $serializer;
		$this->categoryRepository = $categoryRepository;
	}
	
	public function __invoke(string $slug, int $page, Request $request): JsonResponse
	{
		$category = $this->getCategoryRepository()->findOneBy(['slug' => $slug]);
		
		$limit = (int) $request->get('limit', 10);
		
		$qb = $this->getQueryBuilder()
		           ->select('p')
		           ->from('App:Post', 'p')
		           ->where('p.category = :category')
		           ->andWhere('p.publishedAt IS NOT NULL')
		           ->andWhere('p.trashedAt IS NULL')
		           ->setParameter('category', $category)
		           ->orderBy('p.createdAt', 'DESC')
		           ->getQuery()
		;
		
		$pagination = $this->getPaginator()->paginate($qb, $page, $limit);
		
		$currentPage = $pagination->getCurrentPageNumber();
		$lastPage    = ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage());
		
		$posts = [];
		
		foreach ($pagination->getItems() as $item) {
			
			if ($this->isGranted(PostVoter::VIEW, $item)) {
				
				$posts[] = $this->getSerializer()->normalize(
					$item,
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
	
	public function getCategoryRepository(): PostCategoryRepository
	{
		return $this->categoryRepository;
	}
}