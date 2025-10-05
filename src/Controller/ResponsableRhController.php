<?php

namespace App\Controller;

use App\Entity\Employee;
use App\Entity\EmployeeContrat;
use App\Entity\Dossier;
use App\Entity\Document;
use App\Form\EmployeeType;
use App\Form\EmployeeContratType;
use App\Form\DossierType;
use App\Form\DocumentType;
use App\Repository\EmployeeRepository;
use App\Repository\EmployeeContratRepository;
use App\Repository\DossierRepository;
use App\Repository\DocumentRepository;
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
    public function manageEmployes(EmployeeRepository $employeeRepository): Response
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
        $employees = $employeeRepository->findByRole('ROLE_EMPLOYEE');

        $response = $this->render('responsable-rh/employes.html.twig', [
            'employees' => $employees
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

        $employee = new Employee();
        $form = $this->createForm(EmployeeType::class, $employee, [
            'is_new' => true // Nouvel utilisateur
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définir automatiquement le rôle d'employé
            $employee->setRoles(['ROLE_EMPLOYEE']);
            
            // Hasher le mot de passe (obligatoire pour les nouveaux utilisateurs)
            $plainPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($employee, $plainPassword);
            $employee->setPassword($hashedPassword);
            
            $entityManager->persist($employee);
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
    public function editEmploye(Request $request, Employee $employee, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
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
        if (!in_array('ROLE_EMPLOYEE', $employee->getRoles())) {
            $this->addFlash('error', 'Utilisateur non trouvé ou non autorisé.');
            return $this->redirectToRoute('responsable_manage_employes');
        }

        $form = $this->createForm(EmployeeType::class, $employee, [
            'is_new' => false // Modification d'utilisateur existant
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Maintenir le rôle d'employé (pas de changement nécessaire)
            // Le rôle reste inchangé lors de la modification
            
            // Si un nouveau mot de passe est fourni, le hasher
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($employee, $plainPassword);
                $employee->setPassword($hashedPassword);
            }
            
            $entityManager->flush();

            $this->addFlash('success', 'Employé modifié avec succès !');
            return $this->redirectToRoute('responsable_manage_employes');
        }

        $response = $this->render('responsable-rh/edit_employe.html.twig', [
            'form' => $form->createView(),
            'employee' => $employee
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/employes/supprimer/{id}', name: 'responsable_delete_employe')]
    public function deleteEmploye(Employee $employee, EntityManagerInterface $entityManager): Response
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
        if (!in_array('ROLE_EMPLOYEE', $employee->getRoles())) {
            $this->addFlash('error', 'Utilisateur non trouvé ou non autorisé.');
            return $this->redirectToRoute('responsable_manage_employes');
        }

        $entityManager->remove($employee);
        $entityManager->flush();

        $this->addFlash('success', 'Employé supprimé avec succès !');
        return $this->redirectToRoute('responsable_manage_employes');
    }

    // Gestion des contrats
    #[Route('/contrats', name: 'responsable_manage_contrats')]
    public function manageContrats(EmployeeContratRepository $employeeContratRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $contrats = $employeeContratRepository->findActiveContrats();

        $response = $this->render('responsable-rh/contrats.html.twig', [
            'contrats' => $contrats
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/contrats/ajouter', name: 'responsable_add_contrat')]
    public function addContrat(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $contrat = new EmployeeContrat();
        $form = $this->createForm(EmployeeContratType::class, $contrat);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contrat);
            $entityManager->flush();

            $this->addFlash('success', 'Contrat ajouté avec succès !');
            return $this->redirectToRoute('responsable_manage_contrats');
        }

        $response = $this->render('responsable-rh/add_contrat.html.twig', [
            'form' => $form->createView()
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    // Gestion des dossiers
    #[Route('/dossiers', name: 'responsable_manage_dossiers')]
    public function manageDossiers(DossierRepository $dossierRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $dossiers = $dossierRepository->findRecentDossiers(50);

        $response = $this->render('responsable-rh/dossiers.html.twig', [
            'dossiers' => $dossiers
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/dossiers/ajouter', name: 'responsable_add_dossier')]
    public function addDossier(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $dossier = new Dossier();
        $form = $this->createForm(DossierType::class, $dossier);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dossier);
            $entityManager->flush();

            $this->addFlash('success', 'Dossier ajouté avec succès !');
            return $this->redirectToRoute('responsable_manage_dossiers');
        }

        $response = $this->render('responsable-rh/add_dossier.html.twig', [
            'form' => $form->createView()
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    // Gestion des documents
    #[Route('/documents', name: 'responsable_manage_documents')]
    public function manageDocuments(DocumentRepository $documentRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $documents = $documentRepository->findRecentDocuments(50);

        $response = $this->render('responsable-rh/documents.html.twig', [
            'documents' => $documents
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/documents/ajouter', name: 'responsable_add_document')]
    public function addDocument(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document, [
            'is_new' => true
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload du fichier
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('documents_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                    return $this->redirectToRoute('responsable_add_document');
                }

                $document->setFilePath($this->getParameter('documents_directory') . '/' . $newFilename);
                $document->setFileType($file->getMimeType());
                $document->setUploadedBy($this->getUser()->getEmail());
            }

            $entityManager->persist($document);
            $entityManager->flush();

            $this->addFlash('success', 'Document ajouté avec succès !');
            return $this->redirectToRoute('responsable_manage_documents');
        }

        $response = $this->render('responsable-rh/add_document.html.twig', [
            'form' => $form->createView()
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }
}
