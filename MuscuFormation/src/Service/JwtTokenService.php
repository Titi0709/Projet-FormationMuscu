<?php
// src/Service/JwtTokenService.php

namespace App\Service;

use App\Entity\JwtToken;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtTokenService
{
    private EntityManagerInterface $entityManager;
    private string $secretKey;

    public function __construct(EntityManagerInterface $entityManager, string $secretKey)
    {
        $this->entityManager = $entityManager;
        $this->secretKey = $secretKey;
    }

    public function createToken(Utilisateur $utilisateur): JwtToken
    {
        $payload = [
            'email' => $utilisateur->getEmail(),
            'id' => $utilisateur->getId(),
            'exp' => (new \DateTime())->modify('+1 hour')->getTimestamp(),
        ];

        $jwt = JWT::encode($payload, $this->secretKey, 'HS256');

        $token = new JwtToken();
        $token->setToken($jwt);
        
        $expiresAt = (new \DateTime())->modify('+1 hour');
        $token->setExpiresAt($expiresAt);
        $token->setUtilisateur($utilisateur);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    public function validateToken(string $token): ?JwtToken
    {
        $jwtToken = $this->entityManager->getRepository(JwtToken::class)->findOneBy(['token' => $token]);

        // Valider la date d'expiration et la validité du jeton
        if ($jwtToken && $jwtToken->getExpiresAt() > new \DateTime()) {
            return $jwtToken;
        }

        return null;
    }

    public function decodeJwt(string $jwt): ?Utilisateur
    {
        try {
            // Décoder le JWT
            $decoded = JWT::decode($jwt, new Key($this->secretKey, 'HS256'));
            
            // Trouver l'utilisateur dans la base de données
            $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($decoded->id);
            return $utilisateur;
        } catch (\Exception $e) {
            // Si l'exception est levée, cela veut dire que le jeton est invalide ou expiré
            return null;
        }
    }
}
