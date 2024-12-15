<?php


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

class SecheController extends AbstractController
{
    
    private function getSessionUser(Request $request, EntityManagerInterface $entityManager): ?Utilisateur
    {
        $userData = $request->getSession()->get('user');

        if (is_array($userData) && isset($userData['id'])) {
            // Recharger UTI DB
            return $entityManager->getRepository(Utilisateur::class)->find($userData['id']);
        }

        return null;
    }

    #[Route('/Seche/programme/{id}', name: 'Seche_programme')]
    public function showProgramme($id, EntityManagerInterface $entityManager): Response
    {
        $programme = $entityManager->getRepository(Programme::class)->find($id);
        
        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

        $avisList = $entityManager->getRepository(Avis::class)->findBy(['programme' => $programme]);

        return $this->render('Seche/programme_detail.html.twig', [
            'programme' => $programme,
            'avisList' => $avisList,
        ]);
    }

    #[Route('/Seche/programme/{id}/ajouter-avis', name: 'Seche_ajouter_avis')]
    public function addReview(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $commentaire = $request->request->get('commentaire');
        $note = $request->request->get('note');

        if (!$commentaire || !$note) {
            $this->addFlash('error', 'Veuillez fournir un commentaire et une note.');
            return $this->redirectToRoute('Seche_programme', ['id' => $id]);
        }

        $programme = $entityManager->getRepository(Programme::class)->find($id);

        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

        
        $utilisateur = $this->getSessionUser($request, $entityManager);

        if (!$utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour ajouter un avis.');
        }

        $avis = new Avis();
        $avis->setCommentaire($commentaire);
        $avis->setNote((int)$note);
        $avis->setDateAvis(new \DateTime());
        $avis->setProgramme($programme);
        $avis->setUtilisateur($utilisateur);

        $entityManager->persist($avis);
        $entityManager->flush();

        $this->addFlash('success', 'Avis ajouté avec succès.');
        return $this->redirectToRoute('Seche_programme', ['id' => $id]);
    }

    #[Route('/Seche/programme/{id}/acheter', name: 'Seche_acheter_programme')]
    public function purchaseProgramme(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $programme = $entityManager->getRepository(Programme::class)->find($id);

        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

       
        $utilisateur = $this->getSessionUser($request, $entityManager);

        if (!$utilisateur) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour effectuer un achat.');
        }

        $achat = new Achat();
        $achat->setProgramme($programme);
        $achat->setUtilisateur($utilisateur);
        $achat->setDateAchat(new \DateTime());

        $entityManager->persist($achat);
        $entityManager->flush();

        $this->addFlash('success', 'Programme acheté avec succès.');
        return $this->redirectToRoute('Seche_programme', ['id' => $id]);
    }
}
