<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Entity\ImageCategory;
use App\Entity\ImageComment;
use App\Entity\Post;
use App\Entity\PostCategory;
use App\Entity\PostComment;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\UserProfile;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
	/**
	 * @Route("/admin", name="admin")
	 */
	public function index(): Response
	{
		return parent::index();
	}
	
	public function configureDashboard(): Dashboard
	{
		return
			Dashboard::new()
			         ->setTitle('Niitlife Media Admin Dashboard')
			;
	}
	
	public function configureMenuItems(): iterable
	{
		yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
		yield MenuItem::linkToCrud('Images', 'icon class', Image::class);
		yield MenuItem::linkToCrud('Image Categories', 'icon class', ImageCategory::class);
		yield MenuItem::linkToCrud('Image Comments', 'icon class', ImageComment::class);
		yield MenuItem::linkToCrud('Posts', 'icon class', Post::class);
		yield MenuItem::linkToCrud('Post Categories', 'icon class', PostCategory::class);
		yield MenuItem::linkToCrud('Post Comments', 'icon class', PostComment::class);
		yield MenuItem::linkToCrud('Tags', 'icon class', Tag::class);
		yield MenuItem::linkToCrud('Users', 'icon class', User::class);
		yield MenuItem::linkToCrud('User Profiles', 'icon class', UserProfile::class);
	}
}
