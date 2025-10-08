<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Entity\EmployeeContrat;
use App\Entity\Dossier;
use App\Entity\Document;
use App\Entity\Demande;
use App\Entity\Placard;
use App\Entity\NatureContrat;
use App\Entity\Organisation;
use App\Entity\OrganisationEmployeeContrat;
use App\Form\EmployeeType;
use App\Form\DossierType;
use App\Form\DocumentType;
use App\Form\ReponseDemandeType;
use App\Form\PlacardType;
use App\Repository\EmployeRepository;
use App\Repository\EmployeeContratRepository;
use App\Repository\DossierRepository;
use App\Repository\DocumentRepository;
use App\Repository\DemandeRepository;
use App\Repository\PlacardRepository;
use App\Repository\NatureContratRepository;
use App\Repository\NatureContratTypeDocumentRepository;
use App\Repository\OrganisationRepository;
use App\Form\OrganisationType;
use App\Form\OrganisationEmployeeContratType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
        
        // Rediriger vers le nouveau dashboard avec KPI
        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/employes', name: 'responsable_manage_employes')]
    public function manageEmployes(Request $request, EmployeRepository $employeRepository): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        // Récupérer le filtre de statut
        $showAll = $request->query->getBoolean('show_all', false);
        
        // Récupérer les employés selon le filtre
        if ($showAll) {
            $employees = $employeRepository->findByRole('ROLE_EMPLOYEE');
        } else {
            $employees = $employeRepository->findActiveByRole('ROLE_EMPLOYEE');
        }

        $response = $this->render('responsable-rh/employes.html.twig', [
            'employees' => $employees,
            'showAll' => $showAll
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/employes/add', name: 'responsable_rh_add_employe')]
    public function addEmployee(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, NatureContratRepository $natureContratRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $employee = new Employe();
        $form = $this->createForm(EmployeeType::class, $employee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définir le rôle d'employé
            $employee->setRoles(['ROLE_EMPLOYEE']);
            
            // Hacher le mot de passe
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($employee, $plainPassword);
                $employee->setPassword($hashedPassword);
            }

            // Créer le contrat
            $contrat = new EmployeeContrat();
            $contrat->setEmploye($employee);
            $contrat->setNatureContrat($form->get('natureContrat')->getData());
            
            // Vérifier que la date de début n'est pas nulle
            $dateDebut = $form->get('dateDebutContrat')->getData();
            if ($dateDebut === null) {
                $this->addFlash('error', 'La date de début du contrat est obligatoire.');
                return $this->render('responsable-rh/add_employe.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            $contrat->setDateDebut($dateDebut);
            
            // La date de fin est optionnelle
            $dateFin = $form->get('dateFinContrat')->getData();
            if ($dateFin !== null) {
                $contrat->setDateFin($dateFin);
            }
            
            $contrat->setStatut('actif');

            $entityManager->persist($employee);
            $entityManager->persist($contrat);
            $entityManager->flush();

            $this->addFlash('success', 'Employé créé avec succès avec son contrat.');
            return $this->redirectToRoute('responsable_manage_employes');
        }

        return $this->render('responsable-rh/add_employe.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/employes/ajouter', name: 'responsable_add_employe')]
    public function addEmploye(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, NatureContratRepository $natureContratRepository, OrganisationRepository $organisationRepository): Response
    {
        // Vérifier que l'utilisateur est toujours authentifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }
        
        // Vérifier que l'utilisateur a le bon rôle
        if (!in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $employee = new Employe();
        $form = $this->createForm(EmployeeType::class, $employee);

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

               // Créer le contrat principal si les données sont fournies
               $natureContratId = $form->get('natureContrat')->getData();
               $dateDebutContrat = $form->get('dateDebutContrat')->getData();
               $dateFinContrat = $form->get('dateFinContrat')->getData();
               
               if ($natureContratId && $dateDebutContrat) {
                   $natureContrat = $natureContratRepository->find($natureContratId);
                   
                   if ($natureContrat) {
                       $contrat = new EmployeeContrat();
                       $contrat->setEmploye($employee);
                       $contrat->setNatureContrat($natureContrat->getDesignation());
                       $contrat->setDateDebut($dateDebutContrat);
                       $contrat->setDateFin($dateFinContrat);
                       $contrat->setStatut('actif');
                       
                       $entityManager->persist($contrat);
                       
                       // Assigner à l'organisation si sélectionnée
                       $organisationId = $form->get('organisation')->getData();
                       if ($organisationId) {
                           $organisation = $organisationRepository->find($organisationId);
                           
                           if ($organisation) {
                               $orgEmployeeContrat = new OrganisationEmployeeContrat();
                               $orgEmployeeContrat->setEmployeeContrat($contrat);
                               $orgEmployeeContrat->setOrganisation($organisation);
                               $orgEmployeeContrat->setDateDebut($dateDebutContrat);
                               $orgEmployeeContrat->setDateFin($dateFinContrat);
                               
                               $entityManager->persist($orgEmployeeContrat);
                           }
                       }
                   }
               }
               
               // Créer le contrat secondaire si les données sont fournies
               $natureContratId2 = $form->get('natureContrat2')->getData();
               $dateDebutContrat2 = $form->get('dateDebutContrat2')->getData();
               $dateFinContrat2 = $form->get('dateFinContrat2')->getData();
               
               if ($natureContratId2 && $dateDebutContrat2) {
                   $natureContrat2 = $natureContratRepository->find($natureContratId2);
                   
                   if ($natureContrat2) {
                       $contrat2 = new EmployeeContrat();
                       $contrat2->setEmploye($employee);
                       $contrat2->setNatureContrat($natureContrat2->getDesignation());
                       $contrat2->setDateDebut($dateDebutContrat2);
                       $contrat2->setDateFin($dateFinContrat2);
                       $contrat2->setStatut('actif');
                       
                       $entityManager->persist($contrat2);
                       
                       // Assigner à l'organisation secondaire si sélectionnée
                       $organisationId2 = $form->get('organisation2')->getData();
                       if ($organisationId2) {
                           $organisation2 = $organisationRepository->find($organisationId2);
                           
                           if ($organisation2) {
                               $orgEmployeeContrat2 = new OrganisationEmployeeContrat();
                               $orgEmployeeContrat2->setEmployeeContrat($contrat2);
                               $orgEmployeeContrat2->setOrganisation($organisation2);
                               $orgEmployeeContrat2->setDateDebut($dateDebutContrat2);
                               $orgEmployeeContrat2->setDateFin($dateFinContrat2);
                               
                               $entityManager->persist($orgEmployeeContrat2);
                           }
                       }
                   }
               }
               
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


    #[Route('/employes/toggle-status/{id}', name: 'responsable_toggle_employe_status')]
    public function toggleEmployeStatus(Employe $employee, EntityManagerInterface $entityManager): Response
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

        // Toggle le statut actif/inactif
        $employee->setIsActive(!$employee->isActive());
        $entityManager->flush();

        $status = $employee->isActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Employé {$status} avec succès !");
        return $this->redirectToRoute('responsable_manage_employes');
    }

    #[Route('/employes/{id}/details', name: 'responsable_view_employe_details')]
    public function viewEmployeDetails(int $id, EmployeRepository $employeRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $employe = $employeRepository->find($id);
        if (!$employe) {
            $this->addFlash('error', 'Employé non trouvé !');
            return $this->redirectToRoute('responsable_manage_employes');
        }

        $response = $this->render('responsable-rh/employe_details.html.twig', [
            'employe' => $employe
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

        $dossiers = $dossierRepository->findRecentDossiersWithDocuments(50);

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

    #[Route('/dossiers/modifier/{id}', name: 'responsable_edit_dossier')]
    public function editDossier(int $id, Request $request, EntityManagerInterface $entityManager, DossierRepository $dossierRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $dossier = $dossierRepository->find($id);
        if (!$dossier) {
            $this->addFlash('error', 'Dossier non trouvé !');
            return $this->redirectToRoute('responsable_manage_dossiers');
        }

        $form = $this->createForm(DossierType::class, $dossier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Dossier modifié avec succès !');
            return $this->redirectToRoute('responsable_manage_dossiers');
        }

        return $this->render('responsable-rh/edit_dossier.html.twig', [
            'form' => $form->createView(),
            'dossier' => $dossier
        ]);
    }

    #[Route('/dossiers/supprimer/{id}', name: 'responsable_delete_dossier')]
    public function deleteDossier(int $id, EntityManagerInterface $entityManager, DossierRepository $dossierRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $dossier = $dossierRepository->find($id);
        if (!$dossier) {
            $this->addFlash('error', 'Dossier non trouvé !');
            return $this->redirectToRoute('responsable_manage_dossiers');
        }

        $entityManager->remove($dossier);
        $entityManager->flush();

        $this->addFlash('success', 'Dossier supprimé avec succès !');
        return $this->redirectToRoute('responsable_manage_dossiers');
    }

    #[Route('/dossiers/{id}/documents', name: 'responsable_view_dossier_documents')]
    public function viewDossierDocuments(int $id, DossierRepository $dossierRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $dossier = $dossierRepository->find($id);
        if (!$dossier) {
            $this->addFlash('error', 'Dossier non trouvé !');
            return $this->redirectToRoute('responsable_manage_dossiers');
        }

        $response = $this->render('responsable-rh/dossier_documents.html.twig', [
            'dossier' => $dossier,
            'documents' => [] // Les documents ne sont plus liés aux dossiers
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
    public function addDocument(Request $request, EntityManagerInterface $entityManager, DossierRepository $dossierRepository, NatureContratTypeDocumentRepository $matrixRepository, NatureContratRepository $natureContratRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $document = new Document();
        
        // Récupérer les données de la matrice pour l'affichage
        $matrixData = $matrixRepository->findAll();
        $natureContrats = $natureContratRepository->findAll();
        
        // Préparer la matrice pour l'affichage
        $displayMatrix = [];
        foreach ($matrixData as $item) {
            $displayMatrix[$item->getDocumentAbbreviation()][$item->getContractType()] = $item->isRequired();
        }
        
        $form = $this->createForm(DocumentType::class, $document);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier et générer une référence unique
            $reference = $document->getReference();
            if (!$reference || $this->referenceExists($entityManager, $reference)) {
                $document->setReference($this->generateUniqueReference($entityManager));
            }
            
            // Gérer l'upload du fichier
            $file = $form->get('file')->getData();
            if ($file) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $originalExtension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
                
                // Validation de l'extension
                $allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($originalExtension, $allowedExtensions)) {
                    $this->addFlash('error', 'Type de fichier non autorisé. Formats acceptés : PDF, DOC, DOCX, JPG, PNG, GIF');
                    return $this->redirectToRoute('responsable_add_document');
                }
                
                $safeFilename = $this->sanitizeFilename($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$originalExtension;

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
                
                // Déterminer le type MIME basé sur l'extension
                $extension = strtolower($originalExtension);
                $mimeTypes = [
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'bmp' => 'image/bmp',
                    'tiff' => 'image/tiff',
                    'txt' => 'text/plain',
                    'xls' => 'application/vnd.ms-excel',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'ppt' => 'application/vnd.ms-powerpoint',
                    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'zip' => 'application/zip',
                    'rar' => 'application/x-rar-compressed',
                    'mp4' => 'video/mp4',
                    'avi' => 'video/x-msvideo',
                    'mov' => 'video/quicktime',
                    'wmv' => 'video/x-ms-wmv',
                    'mp3' => 'audio/mpeg',
                    'wav' => 'audio/wav',
                    'flac' => 'audio/flac'
                ];
                $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
                
                $document->setFileType($mimeType);
                $document->setUploadedBy($this->getUser()->getEmail());
            }

            $entityManager->persist($document);
            $entityManager->flush();

            $this->addFlash('success', 'Document ajouté avec succès !');
            
            // Rediriger vers le dossier si un dossier_id était fourni
            if ($dossierId) {
                return $this->redirectToRoute('responsable_view_dossier_documents', ['id' => $dossierId]);
            }
            
            return $this->redirectToRoute('responsable_manage_dossiers');
        }

        $response = $this->render('responsable-rh/add_document.html.twig', [
            'form' => $form->createView(),
            'matrixData' => $displayMatrix,
            'natureContrats' => $natureContrats
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/documents/modifier/{id}', name: 'responsable_edit_document')]
    public function editDocument(int $id, Request $request, EntityManagerInterface $entityManager, DocumentRepository $documentRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $document = $documentRepository->find($id);
        if (!$document) {
            $this->addFlash('error', 'Document non trouvé !');
            return $this->redirectToRoute('responsable_manage_documents');
        }

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            
            if ($file) {
                // Supprimer l'ancien fichier s'il existe
                if ($document->getFilePath() && file_exists($document->getFilePath())) {
                    unlink($document->getFilePath());
                }
                
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $originalExtension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                $safeFilename = $this->sanitizeFilename($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$originalExtension;
                
                try {
                    $file->move(
                        $this->getParameter('documents_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                    return $this->redirectToRoute('responsable_edit_document', ['id' => $id]);
                }

                $document->setFilePath($this->getParameter('documents_directory') . '/' . $newFilename);
                
                // Déterminer le type MIME basé sur l'extension
                $extension = strtolower($originalExtension);
                $mimeTypes = [
                    'pdf' => 'application/pdf',
                    'doc' => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'bmp' => 'image/bmp',
                    'tiff' => 'image/tiff',
                    'txt' => 'text/plain',
                    'xls' => 'application/vnd.ms-excel',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'ppt' => 'application/vnd.ms-powerpoint',
                    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'zip' => 'application/zip',
                    'rar' => 'application/x-rar-compressed',
                    'mp4' => 'video/mp4',
                    'avi' => 'video/x-msvideo',
                    'mov' => 'video/quicktime',
                    'wmv' => 'video/x-ms-wmv',
                    'mp3' => 'audio/mpeg',
                    'wav' => 'audio/wav',
                    'flac' => 'audio/flac'
                ];
                $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
                
                $document->setFileType($mimeType);
                $document->setUploadedBy($this->getUser()->getEmail());
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Document modifié avec succès !');
            return $this->redirectToRoute('responsable_manage_documents');
        }

        return $this->render('responsable-rh/add_document.html.twig', [
            'form' => $form->createView(),
            'document' => $document
        ]);
    }

    #[Route('/documents/supprimer/{id}', name: 'responsable_delete_document')]
    public function deleteDocument(int $id, EntityManagerInterface $entityManager, DocumentRepository $documentRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $document = $documentRepository->find($id);
        if (!$document) {
            $this->addFlash('error', 'Document non trouvé !');
            return $this->redirectToRoute('responsable_manage_documents');
        }

        // Supprimer le fichier physique s'il existe
        if ($document->getFilePath() && file_exists($document->getFilePath())) {
            unlink($document->getFilePath());
        }

        $entityManager->remove($document);
        $entityManager->flush();

        $this->addFlash('success', 'Document supprimé avec succès !');
        return $this->redirectToRoute('responsable_manage_documents');
    }

    #[Route('/documents/telecharger/{id}', name: 'responsable_download_document')]
    public function downloadDocument(int $id, DocumentRepository $documentRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $document = $documentRepository->find($id);
        if (!$document || !$document->getFilePath() || !file_exists($document->getFilePath())) {
            $this->addFlash('error', 'Document non trouvé !');
            return $this->redirectToRoute('responsable_manage_documents');
        }

        $response = new BinaryFileResponse($document->getFilePath());
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $document->getFilename()
        );
        
        // Définir manuellement le type MIME pour éviter l'erreur fileinfo
        if ($document->getFileType()) {
            $response->headers->set('Content-Type', $document->getFileType());
        } else {
            // Fallback basé sur l'extension si pas de type MIME stocké
            $extension = strtolower(pathinfo($document->getFilename(), PATHINFO_EXTENSION));
            $mimeTypes = [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'tiff' => 'image/tiff',
                'txt' => 'text/plain',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed',
                'mp4' => 'video/mp4',
                'avi' => 'video/x-msvideo',
                'mov' => 'video/quicktime',
                'wmv' => 'video/x-ms-wmv',
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'flac' => 'audio/flac'
            ];
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
    }

    #[Route('/demandes', name: 'responsable_manage_demandes')]
    public function manageDemandes(DemandeRepository $demandeRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $demandesEnAttente = $demandeRepository->findEnAttente();
        $demandesTraitees = $demandeRepository->findTraiteesParResponsable($this->getUser());

        return $this->render('responsable-rh/demandes.html.twig', [
            'demandesEnAttente' => $demandesEnAttente,
            'demandesTraitees' => $demandesTraitees
        ]);
    }

    #[Route('/demandes/{id}', name: 'responsable_voir_demande')]
    public function voirDemande(int $id, DemandeRepository $demandeRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $demande = $demandeRepository->find($id);
        if (!$demande) {
            $this->addFlash('error', 'Demande/réclamation non trouvée !');
            return $this->redirectToRoute('responsable_manage_demandes');
        }

        return $this->render('responsable-rh/voir_demande.html.twig', [
            'demande' => $demande
        ]);
    }

    #[Route('/demandes/{id}/repondre', name: 'responsable_repondre_demande')]
    public function repondreDemande(int $id, Request $request, DemandeRepository $demandeRepository, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $demande = $demandeRepository->find($id);
        if (!$demande) {
            $this->addFlash('error', 'Demande/réclamation non trouvée !');
            return $this->redirectToRoute('responsable_manage_demandes');
        }

        if ($demande->getStatut() !== 'en_attente') {
            $this->addFlash('error', 'Cette demande/réclamation a déjà été traitée !');
            return $this->redirectToRoute('responsable_manage_demandes');
        }

        $form = $this->createForm(ReponseDemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setResponsableRh($this->getUser());
            $demande->setDateReponse(new \DateTimeImmutable());
            
            $entityManager->flush();

            $statutLibelle = $demande->getStatut() === 'acceptee' ? 'acceptée' : 'refusée';
            $this->addFlash('success', "Demande/réclamation {$statutLibelle} avec succès !");
            return $this->redirectToRoute('responsable_manage_demandes');
        }

        return $this->render('responsable-rh/repondre_demande.html.twig', [
            'demande' => $demande,
            'form' => $form->createView()
        ]);
    }

    // ===== GESTION DES PLACARDS =====

    #[Route('/placards', name: 'responsable_manage_placards')]
    public function managePlacards(PlacardRepository $placardRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $placards = $placardRepository->findAll();

        return $this->render('responsable-rh/placards.html.twig', [
            'placards' => $placards
        ]);
    }

    #[Route('/placards/ajouter', name: 'responsable_add_placard')]
    public function addPlacard(Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $placard = new Placard();
        $form = $this->createForm(PlacardType::class, $placard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($placard);
            $entityManager->flush();

            $this->addFlash('success', 'Placard créé avec succès !');
            return $this->redirectToRoute('responsable_manage_placards');
        }

        return $this->render('responsable-rh/add_placard.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/placards/modifier/{id}', name: 'responsable_edit_placard')]
    public function editPlacard(int $id, Request $request, EntityManagerInterface $entityManager, PlacardRepository $placardRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $placard = $placardRepository->find($id);
        if (!$placard) {
            $this->addFlash('error', 'Placard non trouvé !');
            return $this->redirectToRoute('responsable_manage_placards');
        }

        $form = $this->createForm(PlacardType::class, $placard);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Placard modifié avec succès !');
            return $this->redirectToRoute('responsable_manage_placards');
        }

        return $this->render('responsable-rh/edit_placard.html.twig', [
            'form' => $form->createView(),
            'placard' => $placard
        ]);
    }

    #[Route('/placards/supprimer/{id}', name: 'responsable_delete_placard')]
    public function deletePlacard(int $id, EntityManagerInterface $entityManager, PlacardRepository $placardRepository): Response
    {
        if (!$this->getUser() || !in_array('ROLE_RESPONSABLE_RH', $this->getUser()->getRoles())) {
            return $this->redirectToRoute('app_login');
        }

        $placard = $placardRepository->find($id);
        if (!$placard) {
            $this->addFlash('error', 'Placard non trouvé !');
            return $this->redirectToRoute('responsable_manage_placards');
        }

        // Vérifier s'il y a des dossiers dans ce placard
        if ($placard->getDossiers()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer ce placard car il contient des dossiers !');
            return $this->redirectToRoute('responsable_manage_placards');
        }

        $entityManager->remove($placard);
        $entityManager->flush();

        $this->addFlash('success', 'Placard supprimé avec succès !');
        return $this->redirectToRoute('responsable_manage_placards');
    }

    /**
     * Sanitize filename by removing special characters and converting to lowercase
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove special characters and keep only alphanumeric, dots, hyphens, and underscores
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Convert to lowercase
        $filename = strtolower($filename);
        
        // Remove multiple consecutive dots, hyphens, or underscores
        $filename = preg_replace('/[._-]{2,}/', '_', $filename);
        
        // Remove leading/trailing dots, hyphens, or underscores
        $filename = trim($filename, '._-');
        
        // If filename is empty after sanitization, use a default name
        if (empty($filename)) {
            $filename = 'document';
        }
        
        return $filename;
    }

    private function generateUniqueReference(EntityManagerInterface $entityManager): string
    {
        $year = date('Y');
        $attempts = 0;
        $maxAttempts = 100;
        
        do {
            $attempts++;
            $number = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $reference = "DOC-{$year}-{$number}";
            
            // Vérifier si cette référence existe déjà
            $existingDocument = $entityManager->getRepository(\App\Entity\Document::class)
                ->findOneBy(['reference' => $reference]);
                
        } while ($existingDocument && $attempts < $maxAttempts);
        
        if ($attempts >= $maxAttempts) {
            // Si on n'arrive pas à générer une référence unique, utiliser un timestamp
            $reference = "DOC-{$year}-" . time();
        }
        
        return $reference;
    }

    private function referenceExists(EntityManagerInterface $entityManager, string $reference): bool
    {
        $existingDocument = $entityManager->getRepository(\App\Entity\Document::class)
            ->findOneBy(['reference' => $reference]);
        
        return $existingDocument !== null;
    }

    #[Route('/contrats', name: 'responsable_manage_contrats')]
    public function manageContrats(EmployeeContratRepository $contratRepository): Response
    {
        $contrats = $contratRepository->findAll();
        
        return $this->render('responsable-rh/contrats.html.twig', [
            'contrats' => $contrats,
        ]);
    }


    #[Route('/organisations', name: 'responsable_manage_organisations')]
    public function manageOrganisations(OrganisationRepository $organisationRepository): Response
    {
        $organisations = $organisationRepository->findAll();
        
        return $this->render('responsable-rh/organisations.html.twig', [
            'organisations' => $organisations,
        ]);
    }

    #[Route('/organisations/{id}', name: 'responsable_view_organisation', requirements: ['id' => '\d+'])]
    public function viewOrganisation(int $id, OrganisationRepository $organisationRepository): Response
    {
        $organisation = $organisationRepository->find($id);
        
        if (!$organisation) {
            $this->addFlash('error', 'Organisation non trouvée !');
            return $this->redirectToRoute('responsable_manage_organisations');
        }
        
        return $this->render('responsable-rh/organisation_details.html.twig', [
            'organisation' => $organisation,
        ]);
    }

    #[Route('/organisations/{id}/edit', name: 'responsable_edit_organisation', requirements: ['id' => '\d+'])]
    public function editOrganisation(int $id, Request $request, OrganisationRepository $organisationRepository, EntityManagerInterface $entityManager): Response
    {
        $organisation = $organisationRepository->find($id);
        
        if (!$organisation) {
            $this->addFlash('error', 'Organisation non trouvée !');
            return $this->redirectToRoute('responsable_manage_organisations');
        }
        
        $form = $this->createForm(OrganisationType::class, $organisation);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Organisation mise à jour avec succès !');
            return $this->redirectToRoute('responsable_view_organisation', ['id' => $organisation->getId()]);
        }
        
        return $this->render('responsable-rh/edit_organisation.html.twig', [
            'organisation' => $organisation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/organisations/new', name: 'responsable_add_organisation')]
    public function addOrganisation(Request $request, EntityManagerInterface $entityManager): Response
    {
        $organisation = new Organisation();
        $form = $this->createForm(OrganisationType::class, $organisation);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($organisation);
            $entityManager->flush();
            $this->addFlash('success', 'Organisation créée avec succès !');
            return $this->redirectToRoute('responsable_view_organisation', ['id' => $organisation->getId()]);
        }
        
        return $this->render('responsable-rh/add_organisation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/organisations/assign', name: 'responsable_assign_organisation')]
    public function assignOrganisation(Request $request, EntityManagerInterface $entityManager, OrganisationRepository $organisationRepository, EmployeeContratRepository $employeeContratRepository): Response
    {
        $organisationEmployeeContrat = new \App\Entity\OrganisationEmployeeContrat();
        $form = $this->createForm(OrganisationEmployeeContratType::class, $organisationEmployeeContrat, [
            'organisationRepository' => $organisationRepository,
            'employeeContratRepository' => $employeeContratRepository,
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($organisationEmployeeContrat);
            $entityManager->flush();
            $this->addFlash('success', 'Employé assigné à l\'organisation avec succès !');
            return $this->redirectToRoute('responsable_manage_organisations');
        }
        
        return $this->render('responsable-rh/assign_organisation.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
