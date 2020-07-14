<?php

declare(strict_types = 1);

namespace App\Controller\Tag;

use App\Controller\Concerns\JsonNormalizedMessages;
use App\Controller\Concerns\JsonNormalizedResponse;
use App\Repository\ImageRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use App\Security\Voter\ImageVoter;
use App\Security\Voter\PostVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class FindByIdController extends AbstractController
{
	use JsonNormalizedMessages, JsonNormalizedResponse;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var SerializerInterface
	 */
	private SerializerInterface $serializer;
	
	/**
	 * @var TagRepository
	 */
	private TagRepository $tagRepository;
	
	/**
	 * @var PostRepository
	 */
	private PostRepository $postRepository;
	
	/**
	 * @var ImageRepository
	 */
	private ImageRepository $imageRepository;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		SerializerInterface $serializer,
		TagRepository $tagRepository,
		PostRepository $postRepository,
		ImageRepository $imageRepository
	)
	{
		$this->entityManager   = $entityManager;
		$this->serializer      = $serializer;
		$this->tagRepository   = $tagRepository;
		$this->postRepository  = $postRepository;
		$this->imageRepository = $imageRepository;
	}
	
	public function __invoke(string $id, int $page, Request $request)
	{
		$tag = $this->getTagRepository()->find($id);
		
		if (! $tag) {
			
			return $this->jsonMessage(
				Response::HTTP_NOT_FOUND,
				sprintf(
					'Tag with id "%s" not found',
					$id
				)
			);
		}
		
		$limit = (int) $request->get('limit', 10);
		
		[$postResults, $postsLastPage] =
			$this->getPostRepository()->getForTag(
				$tag, $page, $limit / 2
			)
		;
		
		[$imageResults, $imagesLastPage] =
			$this->getImageRepository()->getForTag(
				$tag, $page, $limit / 2
			)
		;
		
		$posts  = [];
		$images = [];
		
		foreach ($postResults as $post) {
			
			if ($this->isGranted(PostVoter::VIEW, $post)) {
				
				$posts[] = $post;
			}
		}
		
		foreach ($imageResults as $image) {
			
			if ($this->isGranted(ImageVoter::VIEW, $image)) {
				
				$images[] = $image;
			}
		}
		
		return $this->json(
			[
				'currentPage'    => $page,
				'nextPage'       => ++$page,
				'posts'          => $posts,
				'postsLastPage'  => $postsLastPage,
				'images'         => $images,
				'imagesLastPage' => $imagesLastPage,
			]
		);
	}
	
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getSerializer(): SerializerInterface
	{
		return $this->serializer;
	}
	
	public function getTagRepository(): TagRepository
	{
		return $this->tagRepository;
	}
	
	public function getPostRepository(): PostRepository
	{
		return $this->postRepository;
	}
	
	public function getImageRepository(): ImageRepository
	{
		return $this->imageRepository;
	}
}