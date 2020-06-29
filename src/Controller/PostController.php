<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\Contracts\Trashable;
use App\Entity\Post;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/posts")
 */
class PostController extends AbstractController
{
	/**
	 * @var SerializerInterface
	 */
	private SerializerInterface $serializer;
	
	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $entityManager;
	
	/**
	 * @var PostRepository
	 */
	private PostRepository $postRepository;
	
	/**
	 * PostController constructor.
	 * @param  SerializerInterface     $serializer
	 * @param  EntityManagerInterface  $entityManager
	 * @param  PostRepository          $postRepository
	 */
	public function __construct(
		SerializerInterface $serializer,
		EntityManagerInterface $entityManager,
		PostRepository $postRepository
	)
	{
		$this->serializer     = $serializer;
		$this->entityManager  = $entityManager;
		$this->postRepository = $postRepository;
	}
	
	/**
	 * @Route("/{page}", name="post.list", methods={"GET"},defaults={"page": 1 }, requirements={"page"="\d+"})
	 * @param  Request  $request
	 * @return JsonResponse
	 */
	public function list(Request $request): JsonResponse
	{
		$limit = $request->get('limit', 10);
		
		return $this->json($this->getPostRepository()->findAll());
	}
	
	/**
	 * @Route("/{uuid}", name="post.by.uuid", methods={"GET"}, requirements={"uuid"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  Post  $post
	 * @return JsonResponse
	 */
	public function postByUuid(Post $post): JsonResponse
	{
		return $this->json($post);
	}
	
	/**
	 * @Route("/{slug}", name="post.by.slug", methods={"GET"})
	 * @param  Post  $post
	 * @return JsonResponse
	 */
	public function postBySlug(Post $post): JsonResponse
	{
		
		return $this->json($post);
	}
	
	/**
	 * @Route("/", name="post.add", methods={"POST"})
	 * @param  Request  $request
	 * @return JsonResponse
	 */
	public function add(Request $request): JsonResponse
	{
		$post = $this->getSerializer()->deserialize($request->getContent(), Post::class, 'json');
		
		$this->getEntityManager()->persist($post);
		$this->getEntityManager()->flush();
		
		return $this->json($post);
	}
	
	/**
	 * @return JsonResponse
	 */
	public function edit(): JsonResponse
	{
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * @Route("/{uuid}", name="post.delete", methods={"DELETE"}, requirements={"uuid"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  Post  $post
	 * @return JsonResponse
	 */
	public function delete(Post $post): JsonResponse
	{
		$post->trash();
		
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * @Route("/destroy/{uuid}", name="post.delete", methods={"DELETE"}, requirements={"uuid"="[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}"})
	 * @param  Post  $post
	 * @return JsonResponse
	 */
	public function destroy(Post $post): JsonResponse
	{
		if ($post instanceof Trashable) {
			
			if (! $post->isTrashed()) {
				
				return $this->json(null, Response::HTTP_FORBIDDEN);
			}
		}
		
		$this->getEntityManager()->remove($post);
		
		$this->getEntityManager()->flush();
		
		return $this->json(null, Response::HTTP_NO_CONTENT);
	}
	
	/**
	 * @return SerializerInterface
	 */
	public function getSerializer(): SerializerInterface
	{
		return $this->serializer;
	}
	
	/**
	 * @return EntityManagerInterface
	 */
	public function getEntityManager(): EntityManagerInterface
	{
		return $this->entityManager;
	}
	
	public function getPostRepository(): PostRepository
	{
		return $this->postRepository;
	}
}