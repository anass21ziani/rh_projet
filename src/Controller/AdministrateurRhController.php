<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\NatureContrat;
use App\Entity\EmployeeContrat;
use App\Entity\Dossier;
use App\Entity\Placard;
use App\Entity\Document;
use App\Form\EmployeeType;
use App\Form\NatureContratType;
use App\Form\EmployeeContratType;
use App\Form\DossierType;
use App\Form\PlacardType;
use App\Form\DocumentType;
use App\Repository\EmployeeRepository;
use App\Repository\NatureContratRepository;
use App\Repository\EmployeeContratRepository;
use App\Repository\DossierRepository;
use App\Repository\PlacardRepository;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administrateur-rh')]
#[IsGranted('ROLE_ADMINISTRATEUR_RH')]
class AdministrateurRhController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            'form.factory' => '?Symfony\Component\Form\FormFactoryInterface',
        ]);
    }

    #[Route('/dashboard', name: 'administrateur_rh_dashboard')]
    public function dashboard(): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_ADMINISTRATEUR_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }
        
        $response = $this->render('administrateur-rh/dashboard.html.twig');
        
        // Prevent caching of administrateur rh pages
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/responsables-rh', name: 'admin_manage_responsables')]
    public function manageResponsables(EmployeeRepository $employeeRepository): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_ADMINISTRATEUR_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer tous les responsables RH
        $responsables = $employeeRepository->findByRole('ROLE_RESPONSABLE_RH');

        $response = $this->render('administrateur-rh/responsables.html.twig', [
            'responsables' => $responsables
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/responsables-rh/ajouter', name: 'admin_add_responsable')]
    public function addResponsable(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_ADMINISTRATEUR_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee, [
            'is_new' => true // Nouvel utilisateur
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définir automatiquement le rôle de responsable RH
            $employee->setRoles(['ROLE_RESPONSABLE_RH']);
            
            // Hasher le mot de passe (obligatoire pour les nouveaux utilisateurs)
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($employee, $plainPassword);
            $employee->setPassword($hashedPassword);
            
            $entityManager->persist($employee);
            $entityManager->flush();

            $this->addFlash('success', 'Responsable RH ajouté avec succès !');
            return $this->redirectToRoute('admin_manage_responsables');
        }

        $response = $this->render('administrateur-rh/add_responsable.html.twig', [
            'form' => $form->createView()
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/responsables-rh/modifier/{id}', name: 'admin_edit_responsable')]
    public function editResponsable(Request $request, Employee $employee, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_ADMINISTRATEUR_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que c'est bien un responsable RH
        if (!in_array('ROLE_RESPONSABLE_RH', $employee->getRoles())) {
            $this->addFlash('error', 'Utilisateur non trouvé ou non autorisé.');
            return $this->redirectToRoute('admin_manage_responsables');
        }

        $form = $this->createForm(EmployeeType::class, $employee, [
            'is_new' => false // Modification d'utilisateur existant
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Maintenir le rôle de responsable RH (pas de changement nécessaire)
            // Le rôle reste inchangé lors de la modification
            
            // Si un nouveau mot de passe est fourni, le hasher
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($employee, $plainPassword);
                $employee->setPassword($hashedPassword);
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Responsable RH modifié avec succès !');
            return $this->redirectToRoute('admin_manage_responsables');
        }

        $response = $this->render('administrateur-rh/edit_responsable.html.twig', [
            'form' => $form->createView(),
            'employee' => $employee
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/responsables-rh/supprimer/{id}', name: 'admin_delete_responsable')]
    public function deleteResponsable(Employee $employee, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_ADMINISTRATEUR_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que c'est bien un responsable RH
        if (!in_array('ROLE_RESPONSABLE_RH', $employee->getRoles())) {
            $this->addFlash('error', 'Utilisateur non trouvé ou non autorisé.');
            return $this->redirectToRoute('admin_manage_responsables');
        }

        $entityManager->remove($employee);
        $entityManager->flush();

        $this->addFlash('success', 'Responsable RH supprimé avec succès !');
        return $this->redirectToRoute('admin_manage_responsables');
    }

}
