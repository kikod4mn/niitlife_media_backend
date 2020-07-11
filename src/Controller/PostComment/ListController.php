<?php

declare(strict_types = 1);

namespace App\Controller\PostComment;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Repository\PostRepository;
use App\Security\Voter\PostCommentVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ListController extends AbstractController
{
	use JsonNormalizedMessages;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var PaginatorInterface
	 */
	private PaginatorInterface $paginator;
	
	/**
	 * @var PostRepository
	 */
	private PostRepository $postRepository;
	
	/**
	 * @var SerializerInterface
	 */
	private SerializerInterface $serializer;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		PaginatorInterface $paginator,
		PostRepository $postRepository,
		SerializerInterface $serializer
	)
	{
		$this->entityManager  = $entityManager;
		$this->paginator      = $paginator;
		$this->postRepository = $postRepository;
		$this->serializer     = $serializer;
	}
	
	public function __invoke(string $id, int $page, Request $request): JsonResponse
	{
		$post = $this->getPostRepository()->find($id);
		
		if (! $post) {
			
			$this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Post with id "%s" not found. Cannot find comments for a post that does not exist.',
					$id
				)
			);
		}
		
		$qb = $this->getQueryBuilder()
		           ->select('pc')
		           ->from('App\Entity\PostComment', 'pc')
		           ->where('pc.publishedAt IS NOT NULL')
		           ->andWhere('pc.trashedAt IS NULL')
		           ->andWhere('pc.post = :post')
		           ->setParameter('post', $post)
		           ->getQuery()
		;
		
		$limit = $request->get('limit', 10);
		
		$pagination = $this->getPaginator()->paginate($qb, $page, $limit);
		
		$currentPage = $pagination->getCurrentPageNumber();
		$lastPage    = $pagination->getTotalItemCount() / $pagination->getItemNumberPerPage();
		
		$comments = [];
		
		foreach ($pagination->getItems() as $comment) {
			
			if ($this->isGranted(PostCommentVoter::VIEW, $comment)) {
				
				$comments[] = $this->getSerializer()->normalize(
					$comment, 'json', ['groups' => ['comment:list']]
				)
				;
			}
		}
		
		return $this->json(
			[
				'comments'    => $comments,
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
	
	public function getPostRepository(): PostRepository
	{
		return $this->postRepository;
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