<?php

namespace App\Controller;

use App\Repository\IssueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{

    #[Route('/', name: 'app_dashboard')]
    public function index(
        IssueRepository $issueRepository,
    ): Response
    {
        return $this->render('dashboard/index.html.twig', [
            'issues' => $issueRepository->findAll(),
        ]);
    }
}
