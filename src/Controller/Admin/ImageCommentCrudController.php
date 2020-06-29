<?php

namespace App\Controller\Admin;

use App\Entity\ImageComment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ImageCommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ImageComment::class;
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
