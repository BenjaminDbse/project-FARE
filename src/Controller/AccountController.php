<?php


namespace App\Controller;


use App\Repository\ImportRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class AccountController extends AbstractController
{
    /**
     * @Route("/mon-compte", name="account", methods="GET")
     * @param ImportRepository $importRepository
     * @param UserRepository $userRepository
     * @return Response
     */
    public function account(ImportRepository $importRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $imports = $importRepository->findBy(['author' => $user]);
        return $this->render('account/account.html.twig',
        [
            'user' => $user,
            'imports' => $imports,
            'users' => $userRepository->findAll(),
        ]);
    }
}