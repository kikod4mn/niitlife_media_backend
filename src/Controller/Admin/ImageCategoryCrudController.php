<?php

namespace App\Controller\Admin;

use App\Entity\ImageCategory;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ImageCategoryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ImageCategory::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
