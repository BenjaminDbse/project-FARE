<?php

namespace App\Controller;

use App\Repository\ImportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArchiveController extends AbstractController
{
    /**
     * @Route("/archive", name="archive")
     * @param ImportRepository $imports
     * @return Response
     */
    public function index(ImportRepository $imports): Response
    {
        return $this->render('archive/archive.html.twig', [
            'imports' => $imports->findAll(),
        ]);
    }
}
