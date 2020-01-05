<?php

namespace App\Twig;

use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManager;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;
use Twig\Extension\GlobalsInterface;

class NavExtension extends AbstractExtension
{

    protected $em;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->em = $categoryRepository;
    }

    public function categories()
    {
            return ['categoriesGlobal' => $this->em->findAll()];
    }
}