<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->getUser();
        
        // Si l'utilisateur est authentifié, vérifier qu'il a un rôle valide
        if ($user) {
            $roles = $user->getRoles();
            $validRoles = ['ROLE_ADMINISTRATEUR_RH', 'ROLE_RESPONSABLE_RH', 'ROLE_EMPLOYE'];
            
            // Si aucun rôle valide, rediriger vers login
            if (empty(array_intersect($roles, $validRoles))) {
                return $this->redirectToRoute('app_login');
            }
        }
        
        $response = $this->render('home/index.html.twig', [
            'user' => $user
        ]);
        
        // If user is authenticated, prevent caching
        if ($user) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }
        
        return $response;
    }
}