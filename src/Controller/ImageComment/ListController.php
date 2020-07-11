<?php

declare(strict_types = 1);

namespace App\Controller\ImageComment;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Repository\ImageRepository;
use App\Security\Voter\ImageCommentVoter;
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
	 * @var ImageRepository
	 */
	private ImageRepository $imageRepository;
	
	/**
	 * @var SerializerInterface
	 */
	private SerializerInterface $serializer;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		PaginatorInterface $paginator,
		ImageRepository $postRepository,
		SerializerInterface $serializer
	)
	{
		$this->entityManager   = $entityManager;
		$this->paginator       = $paginator;
		$this->imageRepository = $postRepository;
		$this->serializer      = $serializer;
	}
	
	public function __invoke(string $id, int $page, Request $request): JsonResponse
	{
		$image = $this->getImageRepository()->find($id);
		
		if (! $image) {
			
			$this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Post with id "%s" not found. Cannot find comments for a post that does not exist.',
					$id
				)
			);
		}
		
		$qb = $this->getQueryBuilder()
		           ->select('ic')
		           ->from('App\Entity\ImageComment', 'ic')
		           ->where('ic.publishedAt IS NOT NULL')
		           ->andWhere('ic.trashedAt IS NULL')
		           ->andWhere('ic.post = :post')
		           ->setParameter('post', $image)
		           ->getQuery()
		;
		
		$limit = $request->get('limit', 10);
		
		$pagination = $this->getPaginator()->paginate($qb, $page, $limit);
		
		$currentPage = $pagination->getCurrentPageNumber();
		$lastPage    = $pagination->getTotalItemCount() / $pagination->getItemNumberPerPage();
		
		$comments = [];
		
		foreach ($pagination->getItems() as $comment) {
			
			if ($this->isGranted(ImageCommentVoter::VIEW, $comment)) {
				
				$comments[] = $this->getSerializer()->normalize(
					$comment,
					'json',
					['groups' => ['comment:list']]
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
	
	public function getImageRepository(): ImageRepository
	{
		return $this->imageRepository;
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