<?php

namespace App\Controller;

use App\Entity\Label;
use App\Form\LabelType;
use App\Repository\LabelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/labels')]
class LabelController extends AbstractController
{
    #[Route('/', name: 'app_label_index', methods: ['GET'])]
    public function index(LabelRepository $labelRepository): Response
    {
        $labels = $labelRepository->findAll();

        return $this->render('label/index.html.twig', [
            'labels' => $labels,
        ]);
    }

    #[Route('/new', name: 'app_label_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $label = new Label();
        $form = $this->createForm(LabelType::class, $label);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($label);
            $entityManager->flush();

            $this->addFlash('success', 'Label created successfully.');
            return $this->redirectToRoute('app_label_index');
        }

        return $this->render('label/new.html.twig', [
            'label' => $label,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_label_show', methods: ['GET'])]
    public function show(Label $label): Response
    {
        return $this->render('label/show.html.twig', [
            'label' => $label,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_label_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Label $label, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LabelType::class, $label);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Label updated successfully.');
            return $this->redirectToRoute('app_label_index');
        }

        return $this->render('label/edit.html.twig', [
            'label' => $label,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_label_delete', methods: ['POST'])]
    public function delete(Request $request, Label $label, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$label->getId(), $request->request->get('_token'))) {
            $entityManager->remove($label);
            $entityManager->flush();
            $this->addFlash('success', 'Label deleted successfully.');
        }

        return $this->redirectToRoute('app_label_index');
    }
}
