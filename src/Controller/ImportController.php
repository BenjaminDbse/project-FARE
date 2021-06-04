<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Context;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ImportType;
use App\Entity\Import;
use App\Entity\Data;
use App\Service\Recorder;

/**
 * @Route("/import", name="import_")
 */
class ImportController extends AbstractController
{
    const LOCATION_FILE = '/../../public/imports/';


    /**
     * @Route("/", name="import", methods={"GET", "POST"})
     * @param Request $request
     * @param Recorder $recorder
     * @param Context $context
     * @return Response
     */
    public function import(Request $request, Recorder $recorder, Context $context): Response
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
            /** La variable user est une instance de l'entité User
             * @var User $user
             */
            $user = $this->getUser();
            $import->setAuthor($user);
            $import->setCategory($form->get('category')->getData());
            $entityManager->persist($import);
            $dataFile = $form->get('file')->getData();
            $nameFile = $this->moveAndNameFile($dataFile);
            $treatment = fopen(__DIR__ . self::LOCATION_FILE . $nameFile, 'r');
            $type = $form->get('category')->getData()->getid();
            $arrayData = [];

            if ($type === 1) {
                while (($line = fgets($treatment, 137)) !== false) {
                    $arrayData[] = trim($line);
                }
                fclose($treatment);
                unlink(__DIR__ . self::LOCATION_FILE . $nameFile);
                $arrayData = $recorder->treatment($arrayData);
                if (!(key_exists('errors', $arrayData))) {
                    for ($i = 1; $i < count($arrayData) * 3; $i += 3) {
                        try {
                            $blockData = new Data;
                            $blockData->setAdr($arrayData[$i]['adr']);
                            $blockData->setDatetime($arrayData[$i]['date']);
                            $blockData->setStatus($arrayData[$i]['status']);
                            $blockData->setDelta1($arrayData[$i]['data'][0]);
                            $blockData->setDelta2($arrayData[$i]['data'][2]);
                            $blockData->setFilterRatio($arrayData[$i]['data'][4]);
                            $blockData->setTemperatureCorrection($arrayData[$i]['data'][6]);
                            $blockData->setSlopeTemperatureCorrection($arrayData[$i]['data'][8]);
                            $blockData->setRawCo($arrayData[$i]['data'][10]);
                            $blockData->setCoCorrection($arrayData[$i]['data'][12]);
                            if (key_exists('alarm', $arrayData[$i])) {
                                $blockData->setAlarm($arrayData[$i]['alarm']);
                            }
                            $blockData->setImport($import);
                            $entityManager->persist($blockData);
                            $entityManager->flush();
                        } catch (Exception $e) {
                            unset($blockData);
                        }
                    }
                    $this->addFlash('success', 'L\'importation à bien été effectuée');
                    return $this->redirectToRoute('home');
                }
            } elseif ($type === 2) {
                while (($line = fgetcsv($treatment, 50,';')) !== false) {
                    $arrayData[] = trim($line[1]);
                }
                $arrayData = $context->treatment($arrayData);
                dd($arrayData);
                $this->addFlash('success', 'L\'importation à bien été effectuée');
                return $this->redirectToRoute('home');
            }


        }
        return $this->render('import/import.html.twig', [
            'form' => $form->createView(),
            'errors' => $arrayData['errors'] ?? '',
        ]);
    }

    private function moveAndNameFile(object $dataFile): string
    {
        $nameFile = pathinfo($dataFile->getClientOriginalName(), PATHINFO_FILENAME) . '.txt';
        move_uploaded_file($dataFile->getPathName(), __DIR__ . self::LOCATION_FILE . $nameFile);

        return $nameFile;
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param Import $import
     * @ParamConverter("import", class="App\Entity\Import", options={"mapping": {"id": "id"}})
     * @return Response
     */
    public function delete(Request $request, Import $import): Response
    {
        if ($this->isCsrfTokenValid('delete' . $import->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($import);
            $entityManager->flush();
            $this->addFlash('danger', 'L\'Archive à bien été supprimée');
        }
        return $this->redirectToRoute('archive');
    }
}