<?php

namespace App\Controller;

use App\Entity\NatureContratTypeDocument;
use App\Repository\NatureContratTypeDocumentRepository;
use App\Repository\DocumentRepository;
use App\Repository\NatureContratRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/responsable-rh/matrix', name: 'responsable_matrix_')]
#[IsGranted('ROLE_RESPONSABLE_RH')]
class NatureContratTypeDocumentController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(NatureContratTypeDocumentRepository $repository): Response
    {
        // Get all matrix entries grouped by document abbreviation
        $matrixData = $repository->createQueryBuilder('m')
            ->orderBy('m.documentAbbreviation', 'ASC')
            ->addOrderBy('m.contractType', 'ASC')
            ->getQuery()
            ->getResult();

        // Group by document abbreviation for better display
        $groupedMatrix = [];
        foreach ($matrixData as $entry) {
            $groupedMatrix[$entry->getDocumentAbbreviation()][] = $entry;
        }

        return $this->render('responsable-rh/matrix/index.html.twig', [
            'groupedMatrix' => $groupedMatrix,
            'totalEntries' => count($matrixData)
        ]);
    }

    #[Route('/document/{abbreviation}', name: 'by_document', methods: ['GET'])]
    public function byDocument(string $abbreviation, NatureContratTypeDocumentRepository $repository): Response
    {
        $entries = $repository->createQueryBuilder('m')
            ->where('m.documentAbbreviation = :abbreviation')
            ->setParameter('abbreviation', $abbreviation)
            ->orderBy('m.contractType', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('responsable-rh/matrix/by_document.html.twig', [
            'documentAbbreviation' => $abbreviation,
            'entries' => $entries
        ]);
    }

    #[Route('/contract/{contractType}', name: 'by_contract', methods: ['GET'])]
    public function byContract(string $contractType, NatureContratTypeDocumentRepository $repository): Response
    {
        $entries = $repository->createQueryBuilder('m')
            ->where('m.contractType = :contractType')
            ->setParameter('contractType', $contractType)
            ->orderBy('m.documentAbbreviation', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('responsable-rh/matrix/by_contract.html.twig', [
            'contractType' => $contractType,
            'entries' => $entries
        ]);
    }

    #[Route('/statistics', name: 'statistics', methods: ['GET'])]
    public function statistics(NatureContratTypeDocumentRepository $repository): Response
    {
        // Get statistics
        $totalEntries = $repository->count([]);
        $requiredDocuments = $repository->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.required = :required')
            ->setParameter('required', true)
            ->getQuery()
            ->getSingleScalarResult();

        $optionalDocuments = $totalEntries - $requiredDocuments;

        // Get unique document types and contract types
        $documentTypes = $repository->createQueryBuilder('m')
            ->select('DISTINCT m.documentAbbreviation')
            ->orderBy('m.documentAbbreviation', 'ASC')
            ->getQuery()
            ->getResult();

        $contractTypes = $repository->createQueryBuilder('m')
            ->select('DISTINCT m.contractType')
            ->orderBy('m.contractType', 'ASC')
            ->getQuery()
            ->getResult();

        return $this->render('responsable-rh/matrix/statistics.html.twig', [
            'totalEntries' => $totalEntries,
            'requiredDocuments' => $requiredDocuments,
            'optionalDocuments' => $optionalDocuments,
            'documentTypes' => array_column($documentTypes, 'documentAbbreviation'),
            'contractTypes' => array_column($contractTypes, 'contractType')
        ]);
    }
}
