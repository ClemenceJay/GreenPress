<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Repository\StatusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CommandeController extends AbstractController
{
    #[Route('/commandes', name: 'commande_all')]
    public function index(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAll();
        $message = "Toutes les commandes";

        return $this->render('commandes/index.html.twig', [
            'commandes' => $commandes,
            'message' => $message
        ]);
    }

    #[Route('/commandes/payed', name: 'commande_payed')]
    public function commandePayee(CommandeRepository $commandeRepository, StatusRepository $statusRepository): Response
    {
        $status = $statusRepository->findOneBy(['status' => 'Payée']);
        if (!$status) {
            throw $this->createNotFoundException('Le status "Payée" est introuvable.');
        }

        $commandes = $commandeRepository->findBy(['status'=>$status]);
        $message = "Les commandes payées";

        return $this->render('commandes/index.html.twig', [
            'commandes' => $commandes,
            'message' => $message
        ]);
    }

}
