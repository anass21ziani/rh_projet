<?php

namespace App\Controller;

use App\Repository\EmployeRepository;
use App\Repository\DossierRepository;
use App\Repository\DocumentRepository;
use App\Repository\DemandeRepository;
use App\Repository\TypeDocumentRepository;
use App\Repository\EmployeeContratRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        EmployeRepository $employeRepository,
        DossierRepository $dossierRepository,
        DocumentRepository $documentRepository,
        DemandeRepository $demandeRepository,
        TypeDocumentRepository $typeDocumentRepository,
        EmployeeContratRepository $contratRepository
    ): Response {
        $user = $this->getUser();
        $roles = $user ? $user->getRoles() : [];
        
        // Si c'est un employé, afficher son dashboard personnel
        if (in_array('ROLE_EMPLOYEE', $roles)) {
            return $this->renderEmployeeDashboard($user, $contratRepository, $dossierRepository, $documentRepository);
        }
        
        // Sinon, afficher le dashboard global pour les responsables RH et administrateurs
        // KPI généraux
        $kpis = [
            'total_employees' => $employeRepository->count([]),
            'total_dossiers' => $dossierRepository->count([]),
            'total_documents' => $documentRepository->count([]),
            'total_demandes' => $demandeRepository->count([]),
            'total_type_documents' => $typeDocumentRepository->count([]),
            'total_contrats' => $contratRepository->count([]),
        ];

        // KPI pour les dossiers
        $dossiers_stats = [
            'completed' => $dossierRepository->count(['status' => 'completed']),
            'in_progress' => $dossierRepository->count(['status' => 'in_progress']),
            'pending' => $dossierRepository->count(['status' => 'pending']),
        ];

        // KPI pour les documents obligatoires
        $obligatory_docs = $typeDocumentRepository->findObligatoires();
        $obligatory_docs_count = count($obligatory_docs);

        // KPI pour les demandes
        $demandes_stats = [
            'en_attente' => $demandeRepository->countEnAttente(),
            'acceptees' => $demandeRepository->count(['statut' => 'acceptee']),
            'refusees' => $demandeRepository->count(['statut' => 'refusee']),
        ];

        // KPI pour les contrats
        $contrats_stats = [
            'actifs' => $contratRepository->count(['statut' => 'actif']),
            'expires' => $contratRepository->count(['statut' => 'expire']),
            'suspendus' => $contratRepository->count(['statut' => 'suspendu']),
        ];

        // Statistiques par département
        $employees_by_department = $employeRepository->findBy([], ['department' => 'ASC']);
        $department_stats = [];
        foreach ($employees_by_department as $employee) {
            $dept = $employee->getDepartment();
            if (!isset($department_stats[$dept])) {
                $department_stats[$dept] = 0;
            }
            $department_stats[$dept]++;
        }

        // Documents récents
        $recent_documents = $documentRepository->findBy([], ['createdAt' => 'DESC'], 5);

        // Demandes récentes
        $recent_demandes = $demandeRepository->findBy([], ['dateCreation' => 'DESC'], 5);

        return $this->render('dashboard/index.html.twig', [
            'kpis' => $kpis,
            'dossiers_stats' => $dossiers_stats,
            'obligatory_docs_count' => $obligatory_docs_count,
            'demandes_stats' => $demandes_stats,
            'contrats_stats' => $contrats_stats,
            'department_stats' => $department_stats,
            'recent_documents' => $recent_documents,
            'recent_demandes' => $recent_demandes,
        ]);
    }

    private function renderEmployeeDashboard($employee, EmployeeContratRepository $contratRepository, DossierRepository $dossierRepository, DocumentRepository $documentRepository): Response
    {
        // Récupérer les informations de l'employé connecté
        $contrats = $contratRepository->findBy(['employe' => $employee]);
        $dossiers = $dossierRepository->findBy(['employe' => $employee]);
        $documents = $documentRepository->findBy(['dossier' => $dossiers]);

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
}
