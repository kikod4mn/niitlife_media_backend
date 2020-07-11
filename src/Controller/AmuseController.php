<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Service\EntityService\PostService;
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
		
		$raw1 = ['title' => $faker->realText(35), 'body' => $faker->realText(1000)];
		
		$post1 = PostService::create($raw1);
		$post2 = PostService::create(json_encode($raw1));
		
		dump($post1, $post2);
		
		$raw2 = ['title' => 'modified and valid post title', 'body' => ''];
		
		$post3 = PostService::update($raw2, $post1);
		$post4 = PostService::update(json_encode($raw2), $post2);
		
		dump($post3, $post4);
		
		die;
	}
}