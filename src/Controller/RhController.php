<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RhController extends AbstractController
{
    #[Route('/rh', name: 'rh_dashboard')]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_RH');
        
        return $this->render('rh/dashboard.html.twig', [
            'controller_name' => 'RhController',
        ]);
    }
}




