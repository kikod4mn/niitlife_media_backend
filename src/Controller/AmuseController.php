<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Service\EntityService\ImageService;
use App\Service\EntityService\PostService;
use App\Service\EntityService\UserService;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AmuseController extends AbstractController
{
	/**
	 * @Route("/", name="amuse")
	 */
	public function index()
	{
		$faker = Factory::create();
		
		$rawPost1 = ['title' => $faker->realText(35), 'body' => $faker->realText(1000)];
		
		$post1 = PostService::create($rawPost1);
		$post2 = PostService::create(json_encode($rawPost1));
		
		dump($post1, $post2);
		
		$rawPost2 = ['title' => 'modified and valid post title', 'body' => ''];
		
		$post3 = PostService::update($rawPost2, $post1);
		$post4 = PostService::update(json_encode($rawPost2), $post2);
		
		dump($post3, $post4);
		
		$rawUser1 = [
			'username'             => 'kikosad',
			'fullname'             => 'kiko',
			'email'                => 'kiko@kiko.com',
			'plainPassword'        => 'Secret@120',
			'retypedPlainPassword' => 'Secret@120',
		];
		
		$user1 = UserService::create($rawUser1);
		$user2 = UserService::create(json_encode($rawUser1));
		
		dump($user1, $user2);
		
		$rawImage1 = [
			'original'  => 'http://kikopolis.com/img/image.jpg',
			'half'      => 'http://kikopolis.com/img/image_half.jpg',
			'thumbnail' => 'http://kikopolis.com/img/image_thumbnail.jpg',
		
		];
		
		$image1 = ImageService::create($rawImage1);
		$image2 = ImageService::create(json_encode($rawImage1));
		
		dump($image1, $image2);
		
		die;
	}
}