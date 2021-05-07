<?php

namespace App\Controller;

use App\Form\SearchImportType;
use App\Repository\ImportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Datetime;

class ArchiveController extends AbstractController
{
    /**
     * @Route("/archives", name="archive")
     * @param Request $request
     * @param ImportRepository $importRepository
     * @return Response
     */
    public function index(Request $request, ImportRepository $importRepository): Response
    {
        $form = $this->createForm(SearchImportType::class);
        $form->handleRequest($request);
        $imports = $importRepository->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['search'];
            if (!empty($search)) {
                $imports = $importRepository->findLikeName($search);
            }
        }
        return $this->render('archive/archive.html.twig', [
            'imports' => $imports,
            'form' => $form->createView(),
        ]);
    }
}
