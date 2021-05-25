<?php

namespace App\Controller;

use App\Entity\Data;
use App\Entity\Import;
use App\Entity\User;
use App\Form\ImportType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ContextController
 * @package App\Controller
 * @Route("/context", name="context_")
 */
class ContextController extends AbstractController
{
    const LOCATION_FILE = '/../../public/imports/';
    private string $nameFile;

    /**
     * @Route("/context", name="context")
     * @param Request $request
     * @return Response
     */
    public function importContext(Request $request): Response
    {
        if (!($this->getUser())) {
            return $this->redirectToRoute('app_login');
        }
        $import = new import;
        $form = $this->createForm(ImportType::class, $import);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $import->setTitle($form->get('title')->getData());
            $import->setDatetime(new DateTime('now'));
            $import->setType('Contexte');
            /** La variable user est une instance de l'entité User
             * @var User $user
             */
            $user = $this->getUser();
            $import->setAuthor($user);
            $entityManager->persist($import);
            $dataFile = $form->get('file')->getData();
            $this->moveAndNameFile($dataFile);
            $treatment = fopen(__DIR__ . self::LOCATION_FILE . $this->nameFile, 'r');
            while (!feof($treatment)) {
                $blockData = new Data;
                $line = fgets($treatment);

                dd(fread($treatment,filesize($dataFile)));
            }
        fclose($treatment);
        unlink(__DIR__ . self::LOCATION_FILE . $this->nameFile);
        $this->addFlash('success', 'L\'importation à bien été effectuée');
        return $this->redirectToRoute('home');
    }
        return $this->render('context/context.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    private function moveAndNameFile(object $dataFile)
    {
        $this->nameFile = pathinfo($dataFile->getClientOriginalName(), PATHINFO_FILENAME) . '.ctx';
        move_uploaded_file($dataFile->getPathName(), __DIR__ . self::LOCATION_FILE . $this->nameFile);
    }
}
