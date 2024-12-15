<?php


namespace App\Controller;

use App\Entity\Achat;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InfoController extends AbstractController
{
    #[Route('/info', name: 'InfoPayment')]
    public function info(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Récupére utilisateur  session
        $userData = $request->getSession()->get('user');

        if (!$userData || !isset($userData['id'])) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('login');
        }

        // Charger utilisateur  base de données
        $utilisateur = $entityManager->getRepository(Utilisateur::class)->find($userData['id']);
        if (!$utilisateur) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }

        // Récupére achat USER
        $achats = $entityManager->getRepository(Achat::class)->findBy(['utilisateur' => $utilisateur]);

        return $this->render('info/payment_info.html.twig', [
            'utilisateur' => $utilisateur,
            'achats' => $achats,
        ]);
    }
}
