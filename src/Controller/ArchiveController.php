<?php

namespace App\Controller;

use App\Form\SearchImportType;
use App\Repository\ImportRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Datetime;

class ArchiveController extends AbstractController
{
    /**
     * @Route("/archives", name="archive", methods={"GET", "POST"})
     * @param Request $request
     * @param ImportRepository $importRepository
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function index(Request $request, ImportRepository $importRepository, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(SearchImportType::class);
        $form->handleRequest($request);
        $imports = $importRepository->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['search'];
            if (!empty($search)) {
                $imports = $importRepository->findLikeName($search);
            }
            $imports = $paginator->paginate(
                $imports,
                $request->query->getInt('page', 1),
                10
            );
        }
        return $this->render('archive/archive.html.twig', [
            'form' => $form->createView(),
            'imports' => $imports,
        ]);
    }
}
