<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Entity\Demande;
use App\Repository\EmployeRepository;
use App\Repository\EmployeeContratRepository;
use App\Repository\DossierRepository;
use App\Repository\DocumentRepository;
use App\Repository\DemandeRepository;
use App\Form\DemandeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employee')]
#[IsGranted('ROLE_EMPLOYEE')]
class EmployeeController extends AbstractController
{
    #[Route('/dashboard', name: 'employee_dashboard')]
    public function dashboard(
        EmployeRepository $employeRepository,
        EmployeeContratRepository $contratRepository,
        DossierRepository $dossierRepository,
        DocumentRepository $documentRepository
    ): Response {
        $employee = $this->getUser();
        
        // Récupérer les informations de l'employé connecté
        $contrats = $contratRepository->findBy(['employe' => $employee]);
        $dossiers = $dossierRepository->findBy(['employe' => $employee]);
        // Les documents ne sont plus liés aux dossiers, donc on récupère tous les documents
        $documents = $documentRepository->findAll();

        $response = $this->render('employee/dashboard.html.twig', [
            'employee' => $employee,
            'contrats' => $contrats,
            'dossiers' => $dossiers,
            'documents' => $documents
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/profile', name: 'employee_profile')]
    public function profile(): Response
    {
        $employee = $this->getUser();

        $response = $this->render('employee/profile.html.twig', [
            'employee' => $employee
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/contrats', name: 'employee_contrats')]
    public function contrats(EmployeeContratRepository $contratRepository): Response
    {
        $employee = $this->getUser();
        $contrats = $contratRepository->findBy(['employe' => $employee]);

        $response = $this->render('employee/contrats.html.twig', [
            'contrats' => $contrats
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/dossiers', name: 'employee_dossiers')]
    public function dossiers(DossierRepository $dossierRepository): Response
    {
        $employee = $this->getUser();
        $dossiers = $dossierRepository->findBy(['employe' => $employee]);

        $response = $this->render('employee/dossiers.html.twig', [
            'dossiers' => $dossiers
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/documents', name: 'employee_documents')]
    public function documents(DocumentRepository $documentRepository): Response
    {
        $employee = $this->getUser();
        $dossiers = $employee->getDossiers();
        // Les documents ne sont plus liés aux dossiers, donc on récupère tous les documents
        $documents = $documentRepository->findAll();

        $response = $this->render('employee/documents.html.twig', [
            'documents' => $documents
        ]);
        
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    #[Route('/documents/telecharger/{id}', name: 'employee_download_document')]
    public function downloadDocument(int $id, DocumentRepository $documentRepository): Response
    {
        $employee = $this->getUser();
        $document = $documentRepository->find($id);
        
        // Vérifier que le document appartient à l'employé
        if (!$document || $document->getDossier()->getEmploye() !== $employee) {
            $this->addFlash('error', 'Document non trouvé ou non autorisé !');
            return $this->redirectToRoute('employee_documents');
        }

        if (!$document->getFilePath() || !file_exists($document->getFilePath())) {
            $this->addFlash('error', 'Fichier non trouvé !');
            return $this->redirectToRoute('employee_documents');
        }

        $response = new \Symfony\Component\HttpFoundation\BinaryFileResponse($document->getFilePath());
        $response->setContentDisposition(
            \Symfony\Component\HttpFoundation\ResponseHeaderBag::DISPOSITION_ATTACHMENT,
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

    #[Route('/demandes', name: 'employee_demandes')]
    public function mesDemandes(DemandeRepository $demandeRepository): Response
    {
        $employee = $this->getUser();
        $demandes = $demandeRepository->findByEmploye($employee);

        return $this->render('employee/demandes.html.twig', [
            'demandes' => $demandes
        ]);
    }

    #[Route('/demandes/nouvelle', name: 'employee_nouvelle_demande')]
    public function nouvelleDemande(Request $request, EntityManagerInterface $entityManager): Response
    {
        $demande = new Demande();
        $demande->setEmploye($this->getUser());

        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($demande);
            $entityManager->flush();

            $this->addFlash('success', 'Votre demande/réclamation a été envoyée avec succès !');
            return $this->redirectToRoute('employee_demandes');
        }

        return $this->render('employee/nouvelle_demande.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/demandes/{id}', name: 'employee_voir_demande')]
    public function voirDemande(int $id, DemandeRepository $demandeRepository): Response
    {
        $employee = $this->getUser();
        $demande = $demandeRepository->find($id);

        if (!$demande || $demande->getEmploye() !== $employee) {
            $this->addFlash('error', 'Demande/réclamation non trouvée !');
            return $this->redirectToRoute('employee_demandes');
        }

        return $this->render('employee/voir_demande.html.twig', [
            'demande' => $demande
        ]);
    }
}
