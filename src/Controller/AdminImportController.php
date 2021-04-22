<?php


namespace App\Controller;

use App\Entity\Data;
use App\Entity\Import;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ImportRepository;

class AdminImportController extends AbstractController
{
    /**
     * @Route("/{id}", name="import_delete", methods={"DELETE"})
     * @param Request $request
     * @param Import $import
     * @return Response
     */
    public function delete(Request $request, Import $import): Response
    {
        if ($this->isCsrfTokenValid('delete' . $import->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($import);
            $entityManager->flush();
            $this->addFlash('danger', 'L\'importation à bien été supprimée');
        }

        return $this->redirectToRoute('archive');
    }
}