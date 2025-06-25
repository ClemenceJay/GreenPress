<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class StripeController extends AbstractController
{
    #[Route('/stripe/create/link', name: 'app_stripe', methods: ['POST'])]
    public function createPaymentLink(Request $request, ProductRepository $productRepository)
    {

        // Recup des données
        $data = json_decode($request->getContent(), true);

        if (!isset($data['products'])) {
            return new JsonResponse(['error' => 'Invalid amount'], 400);
        }

        $products = $data['products'];
        $amountCalc = 0;
        // faire le total du panier
        foreach ($products as $product) {
            // récupère les données des produits concernés
            $infoProduct = $productRepository->find($product['id']);
            // récupère le prix dans la base et *100 pour en centimes
            $priceProduct = round(($infoProduct->getPrice())*100);
            $totalProduct = $priceProduct*($product['quantity']);
            $amountCalc += $totalProduct;
        }

        $stripeKeyS=$_ENV["StripeKeyS"];
        $stripe = new \Stripe\StripeClient($stripeKeyS);

        $price = $stripe->prices->create([
            'currency' => 'eur',
            'unit_amount' => $amountCalc,
            'product_data' => ['name' => 'Panier Client'],
        ]);
        
        $paymentLink = $stripe->paymentLinks->create([
            'line_items' => [
                [
                    'price' => $price->id,
                    'quantity' => 1,
                ],
            ],
            'after_completion' => [
                'type' => 'redirect',
                'redirect' => ['url' => 'http://localhost:8000'],
            ],
        ]);

        return new JsonResponse(['url' => $paymentLink->url]);
    }

    // #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    // public function createPaymentLink(Request $request, ProductRepository $productRepository)
    // {
    //     Request $request;
    //     EntityManagerInterface $em;
    
    // }
}
