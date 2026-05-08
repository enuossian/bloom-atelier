<?php

namespace App\Service;

use App\Entity\Booking;

/**
 * Gère les interactions avec l'API Stripe.
 * La clé secrète est injectée depuis le paramètre stripe_secret_key (services.yaml)
 * lui-même alimenté par la variable d'environnement STRIPE_SECRET_KEY (.env.local).
 */
class StripeService
{
    public function __construct(
        private readonly string $stripeSecretKey,
    ) {
    }

    /**
     * Crée une session de paiement Stripe à partir d'un Booking.
     * Retourne l'URL de la page de paiement Stripe vers laquelle rediriger l'utilisateur.
     *
     * @param string $successUrl URL de retour après paiement réussi (doit contenir {CHECKOUT_SESSION_ID})
     * @param string $cancelUrl  URL de retour si l'utilisateur annule le paiement
     */
    public function createCheckoutSession(Booking $booking, string $successUrl, string $cancelUrl): string
    {
        \Stripe\Stripe::setApiKey($this->stripeSecretKey);

        // Construire les lignes de commande à partir des BookItems du panier
        $lineItems = [];

        foreach ($booking->getBookItems() as $bookItem) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $bookItem->getSession()->getService()->getName(),
                    ],
                    // Stripe attend le montant en centimes
                    'unit_amount' => (int) ($bookItem->getPrice() * 100),
                ],
                'quantity' => 1,
            ];
        }

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            // Les metadata permettent de retrouver le Booking dans la route success
            'metadata' => [
                'booking_id' => (string) $booking->getId(),
                'reference' => (string) $booking->getReference(),
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        return $session->url;
    }

    /**
     * Récupère une session Stripe par son ID.
     * Utilisé dans la route success pour vérifier le statut du paiement.
     */
    public function retrieveSession(string $sessionId): \Stripe\Checkout\Session
    {
        \Stripe\Stripe::setApiKey($this->stripeSecretKey);

        return \Stripe\Checkout\Session::retrieve($sessionId);
    }
}
