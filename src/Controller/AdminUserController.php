<?php


namespace App\Controller;


use App\Entity\User;
use App\Form\AdminUserType;
use App\Repository\ImportRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/user", name="admin_")
 */
class AdminUserController extends AbstractController
{

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(AdminUserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $choiceUser = $form->get('roleChoice')->getData();
            $user->setRoles(["ROLE_USER"]);
            if ($choiceUser === true) {
                $user->setRoles(["ROLE_ADMIN"]);
            }
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'L\'utilisateur à bien été modifié.');
            return $this->redirectToRoute('account');
        }

        return $this->render('admin/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param User $user
     * @param ImportRepository $importRepository
     * @return Response
     */
    public function delete(Request $request, User $user, ImportRepository $importRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $importsUser = $importRepository->findBy(['author' => $user]);
            foreach ($importsUser as $import) {
                $import->setAuthor(null);
            }
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('danger', 'L\'utilisateur à bien été supprimé.');
        }
        return $this->redirectToRoute('account');
    }
}