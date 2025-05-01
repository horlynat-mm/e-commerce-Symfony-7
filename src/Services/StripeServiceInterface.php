<?php

namespace App\Services;

interface StripeServiceInterface {
    
    public function Paiement($panier, $id_order): string;
    public function getSessionId(): mixed;
    public function getSessionOrder(): mixed;

}