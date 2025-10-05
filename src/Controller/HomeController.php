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
        
        // Si l'utilisateur est authentifié, rediriger vers son dashboard
        if ($user) {
            $roles = $user->getRoles();
            
            if (in_array('ROLE_ADMINISTRATEUR_RH', $roles)) {
                return $this->redirectToRoute('administrateur_rh_dashboard');
            } elseif (in_array('ROLE_RESPONSABLE_RH', $roles)) {
                return $this->redirectToRoute('responsable_rh_dashboard');
            } elseif (in_array('ROLE_EMPLOYEE', $roles)) {
                return $this->redirectToRoute('employee_dashboard');
            } else {
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