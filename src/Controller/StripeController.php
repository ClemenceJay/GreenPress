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
    // Permet de créer la session de paiement qui renvoie vers Stripe
    #[Route('/stripe/create/session', name: 'app_stripe', methods: ['POST'])]
    public function createPaymentSession(Request $request,
        ProductRepository $productRepository,
        ProductCommandeRepository $productCommandeRepository,
        EntityManagerInterface $em,
        StatusRepository $statusRepository
        ) : JsonResponse {

            // Récupère le panier
            $data = json_decode($request->getContent(), true);
            $order = $data['order'] ?? [];
            
        if (empty($order)) {
            return new JsonResponse(['error' => 'Empty order'], 400);
        }
        // Créé une nouvelle commande
        $commande = new Commande();
        $commande->setDate(new \DateTime());

        $amount = 0;
        foreach ($order as $item) {
            // Recherche dans la base les produits correspondants à ceux du panier
            $product = $productRepository->find($item['id']);
            
            if (!$product) {
                continue;
            }

            // Création des données ProductCommande
            $commandeProduct = new ProductCommande();
            $commandeProduct->setCommande($commande);
            $commandeProduct->setPrice($product->getPrice());
            $commandeProduct->setName($product->getName());
            $commandeProduct->setQuantity($item['quantity']);

            $commande->addProductCommande($commandeProduct);
            $productCommandeRepository->save($commandeProduct);

            // calcul du total de la commande
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

        // création des infos prix
        $price = $stripe->prices->create([
            'currency' => 'eur',
            'unit_amount' => round($amount*100),
            'product_data' => ['name' => 'Panier Client'],
        ]);
        
        // ancienne version de création ed lien de paiement
        // $paymentLink = $stripe->paymentLinks->create([
        //     'line_items' => [
        //         [
        //             'price' => $price->id,
        //             'quantity' => 1,
        //         ],
        //     ],
        //     'after_completion' => [
        //         'type' => 'redirect',
        //         'redirect' => ['url' => $_ENV["URL_HOME"]],
        //     ],
        // ]);

        // création de la session
        $session = $stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Panier Client',
                    ],
                    'unit_amount' => round($amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $_ENV["URL_HOME"] . '?success=true',
            'cancel_url' => $_ENV["URL_HOME"] . '?cancel=true',
        ]);

        // Sauvegarde de l’ID Stripe dans la commande pour la retrouver à la validation
        $commande->setStripeSessionId($session->id);
        $em->persist($commande);
        $em->flush();

        // retourne l'URL pour que le front reg=dirige l'user sur la session de paiement Stripe
        return new JsonResponse(['url' => $session->url]);
    }

    // Webhook
    #[Route('/stripe/webhook', name: 'app_stripe_webhook', methods: ['POST'])]
    public function webhook(
        Request $request,
        EntityManagerInterface $em,
        StatusRepository $statusRepository
    ): JsonResponse {

        // récupère les données reçues
        $payload = $request->getContent();
        $signatureHeader = $request->headers->get('stripe-signature');
        $endpointSecret = $_ENV["ENDPOINT_SECRET"];

        // création de l'evenement
        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signatureHeader, $endpointSecret);
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            echo json_encode(['Error parsing payload: ' => $e->getMessage()]);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // invalid signature
            echo 'Webhook error while validating signature.';
            http_response_code(400);
            exit();
        }

        // récupère l'évènement 
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $commande = $em->getRepository(Commande::class)->findOneBy(['stripeSessionId' => $session->id]);
            if ($commande) {
                $status = $statusRepository->findOneBy(['status' => 'Confirmée']);
                if ($status) {
                    $commande->setStatus($status);
                    $em->persist($commande);
                    $em->flush();
                }
            }
        }

        return new JsonResponse(['status' => 'success'], 200);
    }
}
