<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $products = $productRepository->findAll();

        if ($request->query->get('success') === 'true') {
            $this->addFlash('success', 'Votre commande a bien été enregistrée');
        } else if($request->query->get('cancel') === 'true') {
            $this->addFlash('error', 'Une erreur s\'est produite, merci de réessayer ou contactez le service client');
        }

        return $this->render('home/index.html.twig', [
            'products' => $products,
        ]);
    }
}
