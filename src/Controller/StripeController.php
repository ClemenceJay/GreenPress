<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\ProductCommande;
use App\Repository\ProductCommandeRepository;
use App\Repository\ProductRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use \DateTime;

final class StripeController extends AbstractController
{
    #[Route('/stripe/create/link', name: 'app_stripe', methods: ['POST'])]
    public function createPaymentLink(Request $request,
        ProductRepository $productRepository,
        ProductCommandeRepository $productCommandeRepository,
        EntityManagerInterface $em,
        StatusRepository $statusRepository
        ) : JsonResponse {
            $data = json_decode($request->getContent(), true);
            $order = $data['order'] ?? [];
            
        if (empty($order)) {
            return new JsonResponse(['error' => 'Empty order'], 400);
        }

        $commande = new Commande();
        $commande->setDate(new \DateTime());

        $amount = 0;

        foreach ($order as $item) {
            $product = $productRepository->find($item['id']);
            
            if (!$product) {
                continue;
            }

            $commandeProduct = new ProductCommande();
            $commandeProduct->setCommande($commande);
            $commandeProduct->setPrice($product->getPrice());
            $commandeProduct->setName($product->getName());
            $commandeProduct->setQuantity($item['quantity']);

            $commande->addProductCommande($commandeProduct);
            $productCommandeRepository->save($commandeProduct);

            $priceProduct = $commandeProduct->getPrice();
            $totalProduct = $priceProduct*($commandeProduct->getQuantity());
            $amount += $totalProduct;
        }

        $status = $statusRepository->findOneBy(['status' => 'En attente']);
        if (!$status) {
            return new JsonResponse(['error' => 'Status "En attente" not found'], 500);
        }

        $commande->setStatus($status);

        $em->persist($commande);
        $em->flush();

        $stripeKeyS=$_ENV["StripeKeyS"];
        $stripe = new \Stripe\StripeClient($stripeKeyS);

        $price = $stripe->prices->create([
            'currency' => 'eur',
            'unit_amount' => round($amount*100),
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
                'redirect' => ['url' => $_ENV["URL_HOME"]],
            ],
        ]);

        return new JsonResponse(['url' => $paymentLink->url]);
    }

    // #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    // public function webhook(
    //     Request $request,
    //     ProductRepository $productRepository,
    //     EntityManagerInterface $em,
    //     StatutsRepository $statutsRepository
    // ): JsonResponse {
    
    // }
}
