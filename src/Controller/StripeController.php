<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use App\Repository\ProductsRepository;
use App\Services\StripeServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class StripeController extends AbstractController
{
    public function __construct(
        private readonly StripeServiceInterface $stripeServiceInterface,
        private readonly EntityManagerInterface $entityManagerInterface,
        private readonly ProductsRepository $productsRepository,
        private readonly OrderRepository $orderRepository
    )
    {
        
    }

    #[Route('/stripe/success/{order}', name: 'app_stripe_success')]
    public function success($order, Session $session)
    {
        $order = $this->orderRepository->find($order);

        $order->setPaid(true);
        $this->entityManagerInterface->flush();
        
        $session->remove('Panier');
        
        return $this->render('stripe/index.html.twig', [

        ]);
    }
    #[Route('/stripe/cancel/{order}', name: 'app_stripe_cancel')]
    public function cancel($order)
    {
        $order = $this->orderRepository->find($order);
        $this->entityManagerInterface->remove($order);
        $this->entityManagerInterface->flush();

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/stripe', name: 'app_stripe', methods:['GET'])]
    public function index(SessionInterface $session): Response
    {

        $panier = $session->get("Panier", []);
        $data = [];
        $total = 0;

        $order = new Order();
        $this->entityManagerInterface->persist($order);

        foreach ($panier as $key => $quantity) {
            $produit = $this->productsRepository->find($key);

            $data[] = [
                "product" => $produit,
                "quantity" => $quantity
            ];
            $total += $produit->getPrice() * $quantity;
        }

        $order->setAmountTotal($total);
        $order->setPaid(false);
        $order->setPaymentId(1);
        if($this->getUser()){
            $order->setUser($this->getUser());
        }
        $this->entityManagerInterface->flush();
        $url = $this->stripeServiceInterface->Paiement($data, $order);

        return $this->redirect($url, Response::HTTP_SEE_OTHER);

    }
}
