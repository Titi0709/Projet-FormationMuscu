<?php

// src/Controller/ProgrammeController.php

namespace App\Controller;

use App\Entity\Programme;
use App\Entity\Achat; // Assurez-vous d'importer l'entité Achat
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProgrammeController extends AbstractController
{
    #[Route('/programmes', name: 'programmes')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $programmes = $entityManager->getRepository(Programme::class)->findAll();

        return $this->render('programme/programme.html.twig', [
            'programmes' => $programmes,
        ]);
    }

    #[Route('/programme/{id}', name: 'programme_show')]
    public function show(EntityManagerInterface $entityManager, int $id): Response
    {
        $programme = $entityManager->getRepository(Programme::class)->find($id);

        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

        // Créer une nouvelle instance de Achat
        $achat = new Achat();
        $achat->setProgramme($programme); // Liez l'achat au programme
        $achat->setUtilisateur($this->getUser()); // Mettez l'utilisateur courant
        $achat->setDateAchat(new \DateTime()); // Utilisez la bonne méthode pour définir la date d'achat

        // Enregistrer l'achat dans la base de données
        $entityManager->persist($achat);
        $entityManager->flush();

        // Vous pouvez rediriger ou retourner une réponse simple
        return new Response('Achat enregistré avec succès pour le programme : ' . $programme->getTitre());
    }
}
