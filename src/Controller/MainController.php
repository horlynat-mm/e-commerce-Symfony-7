<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    public function __construct(
        private CategorieRepository $categorieRepository,
    ){}

    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        $categorie = $this->categorieRepository->findBy([],["name"=>"asc"]);
        return $this->render('main/index.html.twig', [
            "categories"=>$categorie,
        ]);
    }
}
