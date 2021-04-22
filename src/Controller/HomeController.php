<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ImportRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param ImportRepository $importRepository
     * @return Response
     */
    public function index(ImportRepository $importRepository): Response
    {
        if (!($this->getUser())) {
            return $this->redirectToRoute('app_login');
        }
        $imports = $importRepository->findBy([], ['id' => 'DESC'], 5);
        return $this->render('home/home.html.twig', [
            'imports' => $imports,
        ]);
    }
}
