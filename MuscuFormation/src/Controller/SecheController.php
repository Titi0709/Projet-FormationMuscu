<?php
// src/Controller/SecheController.php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\JwtTokenService;
use App\Entity\Programme;
use App\Entity\Achat;
use App\Entity\Avis;

class SecheController extends AbstractController
{
    private JwtTokenService $jwtTokenService;

    public function __construct(JwtTokenService $jwtTokenService)
    {
        $this->jwtTokenService = $jwtTokenService;
    }

    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $request->getSession()->all(); // Débogage (à retirer plus tard)

        $jwtToken = $request->getSession()->get('jwt_token');
        
        if (!$jwtToken || !is_string($jwtToken)) {
            return $this->redirectToRoute('login');
        }

        $jwtTokenEntity = $this->jwtTokenService->validateToken($jwtToken);

        if (!$jwtTokenEntity || !$jwtTokenEntity->getUtilisateur()) {
            return $this->redirectToRoute('login');
        }

        $utilisateur = $this->jwtTokenService->decodeJwt($jwtTokenEntity->getToken());

        if (!$utilisateur) {
            return $this->redirectToRoute('login');
        }
    }

    #[Route('/Seche/programme/{id}', name: 'Seche_programme')]
    public function showProgramme($id, EntityManagerInterface $entityManager): Response
    {
        $programme = $entityManager->getRepository(Programme::class)->find($id);
        
        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

        return $this->render('Seche/programme_detail.html.twig', [
            'programme' => $programme,
        ]);
    }

    #[Route('/Seche/programme/{id}/ajouter-avis', name: 'Seche_ajouter_avis')]
    public function addReview(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $avisContent = $request->request->get('avis');
        
        if (!$avisContent) {
            $this->addFlash('error', 'Avis non fourni.');
            return $this->redirectToRoute('Seche_programme', ['id' => $id]);
        }

        $programme = $entityManager->getRepository(Programme::class)->find($id);
        
        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

        $avis = new Avis();
        $avis->setContenu($avisContent);
        $avis->setProgramme($programme);
        $avis->setUtilisateur($this->getUser());

        $entityManager->persist($avis);
        $entityManager->flush();

        $this->addFlash('success', 'Avis ajouté avec succès.');
        return $this->redirectToRoute('Seche_programme', ['id' => $id]);
    }

    #[Route('/Seche/avis/{id}/supprimer', name: 'Seche_supprimer_avis')]
    public function deleteReview($id, EntityManagerInterface $entityManager): Response
    {
        $avis = $entityManager->getRepository(Avis::class)->find($id);

        if (!$avis) {
            throw $this->createNotFoundException('Avis non trouvé');
        }

        if ($avis->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($avis);
        $entityManager->flush();

        $this->addFlash('success', 'Avis supprimé avec succès.');
        return $this->redirectToRoute('Seche_programme', ['id' => $avis->getProgramme()->getId()]);
    }

    #[Route('/Seche/programme/{id}/acheter', name: 'Seche_acheter_programme')]
    public function purchaseProgramme(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $programme = $entityManager->getRepository(Programme::class)->find($id);

        if (!$programme) {
            throw $this->createNotFoundException('Programme non trouvé');
        }

        $achatSuccess = $request->request->get('achat_success');
        
        if ($achatSuccess) {
            $achat = new Achat();
            $achat->setProgramme($programme);
            $achat->setUtilisateur($this->getUser());
            $achat->setMontant($programme->getPrix());

            $entityManager->persist($achat);
            $entityManager->flush();

            $this->addFlash('success', 'Achat effectué avec succès.');
        } else {
            $this->addFlash('error', 'L\'achat a échoué. Veuillez réessayer.');
        }

        return $this->redirectToRoute('Seche_programme', ['id' => $id]);
    }
}
