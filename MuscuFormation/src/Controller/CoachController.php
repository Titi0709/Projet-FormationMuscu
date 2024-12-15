<?php


namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Achat;
use App\Entity\Programme;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CoachController extends AbstractController
{
    #[Route('/Coach', name: 'coach_dashboard')]
    public function dashboard(): Response
    {
        
        return $this->render('coach/dashboard.html.twig');
    }

    #[Route('/Coach/utilisateurs', name: 'coach_utilisateurs', methods: ['GET'])]
    public function getCoaches(EntityManagerInterface $entityManager): Response
    {
        // Récupére utilisateurs avec rôle 2 
        $coaches = $entityManager->getRepository(Utilisateur::class)->findBy(['role' => 2]);

        return $this->render('coach/utilisateurs.html.twig', [
            'coaches' => $coaches,
        ]);
    }

    #[Route('/Coach/achats/masse', name: 'coach_achats_masse', methods: ['GET'])]
    public function getMassePurchases(EntityManagerInterface $entityManager): Response
    {
        // Récupérer  pour le programme Masse
        $programmeMasse = $entityManager->getRepository(Programme::class)->findOneBy(['titre' => 'Prise de Masse']);
        $achatsMasse = $programmeMasse
            ? $entityManager->getRepository(Achat::class)->findBy(['programme' => $programmeMasse])
            : [];

        return $this->render('coach/achats_masse.html.twig', [
            'achatsMasse' => $achatsMasse,
        ]);
    }

    #[Route('/Coach/achats/seche', name: 'coach_achats_seche', methods: ['GET'])]
    public function getSechePurchases(EntityManagerInterface $entityManager): Response
    {
        // Récupérer le programme Seche
        $programmeSeche = $entityManager->getRepository(Programme::class)->findOneBy(['titre' => 'Seche']);
        $achatsSeche = $programmeSeche
            ? $entityManager->getRepository(Achat::class)->findBy(['programme' => $programmeSeche])
            : [];

        return $this->render('coach/achats_seche.html.twig', [
            'achatsSeche' => $achatsSeche,
        ]);
    }
}
