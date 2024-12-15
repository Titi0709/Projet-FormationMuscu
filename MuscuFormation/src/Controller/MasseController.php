<?php
// src/Controller/MasseController.php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Programme;
use App\Entity\Avis;
use App\Entity\Achat;
use App\Entity\Utilisateur;

class MasseController extends AbstractController
{
    /**
     * Récupère l'utilisateur connecté depuis la session et la base de données.
     */
    private function getSessionUser(Request $request, EntityManagerInterface $entityManager): ?Utilisateur
    {
        $userData = $request->getSession()->get('user');

        if (is_array($userData) && isset($userData['id'])) {
            // Recharger l'utilisateur depuis la base de données
            return $entityManager->getRepository(Utilisateur::class)->find($userData['id']);
        }

        return null;
    }

    #[Route('/Masse/programme/{id}', name: 'Masse_programme')]
    public function showProgramme($id, EntityManagerInterface $entityManager): Response
    {
        $programme = $entityManager->getRepository(Programme::class)->find($id);
        
        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

        $avisList = $entityManager->getRepository(Avis::class)->findBy(['programme' => $programme]);

        return $this->render('Masse/programme_detail.html.twig', [
            'programme' => $programme,
            'avisList' => $avisList,
        ]);
    }

    #[Route('/Masse/programme/{id}/ajouter-avis', name: 'Masse_ajouter_avis')]
    public function addReview(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $commentaire = $request->request->get('commentaire');
        $note = $request->request->get('note');
    
        if (!$commentaire || !$note) {
            $this->addFlash('error', 'Veuillez fournir un commentaire et une note.');
            return $this->redirectToRoute('Masse_programme', ['id' => $id]);
        }
    
        $programme = $entityManager->getRepository(Programme::class)->find($id);
    
        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }
    
        // Récupérer l'utilisateur depuis la session et la base de données
        $utilisateur = $this->getSessionUser($request, $entityManager);
    
        if (!$utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter un avis.');
        }
    
        // Créer et lier l'avis
        $avis = new Avis();
        $avis->setCommentaire($commentaire);
        $avis->setNote((int) $note);
        $avis->setDateAvis(new \DateTime());
        $avis->setProgramme($programme);
        $avis->setUtilisateur($utilisateur);
    
        // Persister uniquement l'avis, pas l'utilisateur
        $entityManager->persist($avis);
        $entityManager->flush();
    
        $this->addFlash('success', 'Avis ajouté avec succès.');
        return $this->redirectToRoute('Masse_programme', ['id' => $id]);
    }

    #[Route('/Masse/programme/{id}/acheter', name: 'Masse_acheter_programme')]
    public function purchaseProgramme(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $programme = $entityManager->getRepository(Programme::class)->find($id);

        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

        // Récupérer l'utilisateur depuis la session et la base de données
        $utilisateur = $this->getSessionUser($request, $entityManager);

        if (!$utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour effectuer un achat.');
        }

        // Créer l'achat
        $achat = new Achat();
        $achat->setProgramme($programme);
        $achat->setUtilisateur($utilisateur);
        $achat->setDateAchat(new \DateTime());

        // Persister l'achat
        $entityManager->persist($achat);
        $entityManager->flush();

        $this->addFlash('success', 'Programme acheté avec succès.');
        return $this->redirectToRoute('Masse_programme', ['id' => $id]);
    }
}