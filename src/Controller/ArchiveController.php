<?php

namespace App\Controller;

use App\Form\SearchImportType;
use App\Repository\ImportContextRepository;
use App\Repository\ImportRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/archives")
 * */
class ArchiveController extends AbstractController
{
    /**
     * @Route("/enregistrements", name="archive", methods={"GET", "POST"})
     * @param Request $request
     * @param ImportRepository $importRepository
     * @return Response
     */
    public function recorder(
        Request $request,
        ImportRepository $importRepository
    ): Response
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
        return $this->render('archive/recorder.html.twig', [
            'form' => $form->createView(),
            'imports' => $imports,
        ]);
    }
}
