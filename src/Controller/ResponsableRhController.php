<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Form\EmployeType;
use App\Repository\EmployeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/responsable-rh')]
#[IsGranted('ROLE_RESPONSABLE_RH')]
class ResponsableRhController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'form.factory' => '?Symfony\Component\Form\FormFactoryInterface',
        ]);
    }
    #[Route('/dashboard', name: 'responsable_rh_dashboard')]
    public function dashboard(): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }
        
        $response = $this->render('responsable-rh/dashboard.html.twig');
        
        // Prevent caching of responsable rh pages
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/employes', name: 'responsable_manage_employes')]
    public function manageEmployes(EmployeRepository $employeRepository): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer tous les employés
        $employes = $employeRepository->findByRole('ROLE_EMPLOYE');

        $response = $this->render('responsable-rh/employes.html.twig', [
            'employes' => $employes
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/employes/ajouter', name: 'responsable_add_employe')]
    public function addEmploye(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $employe = new Employe();
        $form = $this->createForm(EmployeType::class, $employe, [
            'is_new' => true // Nouvel utilisateur
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définir automatiquement le rôle d'employé
            $employe->setRoles(['ROLE_EMPLOYE']);
            
            // Hasher le mot de passe (obligatoire pour les nouveaux utilisateurs)
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($employe, $plainPassword);
            $employe->setPassword($hashedPassword);
            
            $entityManager->persist($employe);
            $entityManager->flush();

            $this->addFlash('success', 'Employé ajouté avec succès !');
            return $this->redirectToRoute('responsable_manage_employes');
        }

        $response = $this->render('responsable-rh/add_employe.html.twig', [
            'form' => $form->createView()
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/employes/modifier/{id}', name: 'responsable_edit_employe')]
    public function editEmploye(Request $request, Employe $employe, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que c'est bien un employé
        if (!in_array('ROLE_EMPLOYE', $employe->getRoles())) {
            $this->addFlash('error', 'Utilisateur non trouvé ou non autorisé.');
            return $this->redirectToRoute('responsable_manage_employes');
        }

        $form = $this->createForm(EmployeType::class, $employe, [
            'is_new' => false // Modification d'utilisateur existant
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Maintenir le rôle d'employé (pas de changement nécessaire)
            // Le rôle reste inchangé lors de la modification
            
            // Si un nouveau mot de passe est fourni, le hasher
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($employe, $plainPassword);
                $employe->setPassword($hashedPassword);
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Employé modifié avec succès !');
            return $this->redirectToRoute('responsable_manage_employes');
        }

        $response = $this->render('responsable-rh/edit_employe.html.twig', [
            'form' => $form->createView(),
            'employe' => $employe
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/employes/supprimer/{id}', name: 'responsable_delete_employe')]
    public function deleteEmploye(Employe $employe, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que c'est bien un employé
        if (!in_array('ROLE_EMPLOYE', $employe->getRoles())) {
            $this->addFlash('error', 'Utilisateur non trouvé ou non autorisé.');
            return $this->redirectToRoute('responsable_manage_employes');
        }

        $entityManager->remove($employe);
        $entityManager->flush();

        $this->addFlash('success', 'Employé supprimé avec succès !');
        return $this->redirectToRoute('responsable_manage_employes');
    }
}
