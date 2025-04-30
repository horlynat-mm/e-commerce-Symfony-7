<?php

namespace App\Services;

use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService implements StripeServiceInterface
{

    private const STRIPE_PAYEMENT_ID = "session_stripe_payment_id";
    private const STRIPE_PAYEMENT_ORDER = "session_stripe_payment_order";

    public function __construct(
        private string $stripeSecret,
        private UrlGeneratorInterface $urlGenerator,
        private RequestStack $requestStack,
    ) {
        Stripe::SetApiKey($stripeSecret);
    }

    public function Paiement($panier, $id_order): string
    {
        $mySession = $this->requestStack->getSession();
        $session = Session::create([
            'success_url' => $this->urlGenerator->generate('app_stripe_success', ['order' => $id_order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('app_stripe_cancel', ['order' => $id_order->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    $this->getLinesItems($panier),
                ]
            ],
            'mode'=> 'payment',
        ]);

        $mySession->set(self::STRIPE_PAYEMENT_ID, $session->id);
        $mySession->set(self::STRIPE_PAYEMENT_ORDER, $id_order->getId());

        return $session->url;
    }

    public function getSessionId(): mixed
    {
        return $this->requestStack->getSession()->get(self::STRIPE_PAYEMENT_ID);
    }

    public function getSessionOrder(): mixed
    {
        return $this->requestStack->getSession()->get(self::STRIPE_PAYEMENT_ORDER);
    }

    private function getLinesItems($panier): array
    {
        $product = [];

        foreach($panier as $item)
        {
            $product['price_data']['currency'] = "eur";
            $product['price_data']['product_data']['name'] = $item["product"]->getName();
            $product['price_data']['unit_amount'] = $item["product"]->getPrice() * 100;
            $product['quantity'] = $item['quantity'];
            $products[] = $product;
        }

        return $products;
    }
}
