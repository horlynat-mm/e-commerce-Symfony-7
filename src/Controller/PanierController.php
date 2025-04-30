<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class PanierController extends AbstractController
{

    public function __construct(
        private readonly ProductsRepository $productsRepository,
    ) {
    }

    #[Route('/panier', name: 'panier')]
    public function index(SessionInterface $session): Response
    {
        $panier = $session->get("Panier", []);
        $data = [];
        $total = 0;

        foreach ($panier as $key => $quantity) {
            $produit = $this->productsRepository->find($key);

            $data[] = [
                "product" => $produit,
                "quantity" => $quantity
            ];
            $total += $produit->getPrice() * $quantity;
        }


        return $this->render('panier/index.html.twig', [
            "data" => $data,
            "total" => $total,
        ]);
    }

    #[Route('/panier/add/{id}', name: 'addPanier')]
    public function add(int $id, SessionInterface $session): Response
    {
        $panier = $session->get("Panier", []);

        if (empty($panier[$id])) {
            $panier[$id] = 1;
        } else {
            $panier[$id]++;
        }

        $session->set("Panier", $panier);

        return $this->redirectToRoute("panier");
    }

    #[Route('/panier/supp/{id}', name: 'suppPanier')]
    public function supp(int $id, SessionInterface $session): Response
    {
        $panier = $session->get("Panier", []);

        if (!empty($panier[$id]) && $panier[$id] > 1) {
            $panier[$id]--;
        } else {
            unset($panier[$id]);
        }

        $session->set("Panier", $panier);

        return $this->redirectToRoute("panier");
    }

    #[Route('/panier/remove/{id}', name: 'removePanier')]
    public function remove(int $id, SessionInterface $session): Response
    {
        $panier = $session->get("Panier", []);
        if (!empty($panier[$id])) {
            unset($panier[$id]);
        }
        $session->set("Panier", $panier);

        return $this->redirectToRoute("panier");
    }
    
    #[Route('/panier/trash', name: 'trashPanier')]
    public function trash(SessionInterface $session): Response
    {
        $session->remove('Panier');

        return $this->redirectToRoute("panier");
    }
}
