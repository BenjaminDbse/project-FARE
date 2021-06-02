<?php

namespace App\Controller;

use App\Entity\User;
use DateTime;
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
    private Import $import;
    private Data $blockData;


    /**
     * @Route("/", name="import", methods={"GET", "POST"})
     * @param Request $request
     * @param Recorder $recorder
     * @return Response
     * @throws \Exception
     */
    public function import(Request $request, Recorder $recorder): Response
    {
        if (!($this->getUser())) {
            return $this->redirectToRoute('app_login');
        }
        $this->import = new import;
        $form = $this->createForm(ImportType::class, $this->import);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $this->import->setTitle($form->get('title')->getData());
            $this->import->setDatetime(new DateTime('now'));
            /** La variable user est une instance de l'entité User
             * @var User $user
             */
            $user = $this->getUser();
            $this->import->setAuthor($user);
            $entityManager->persist($this->import);
            $dataFile = $form->get('file')->getData();
            $nameFile = $this->moveAndNameFile($dataFile);
            $treatment = fopen(__DIR__ . self::LOCATION_FILE . $nameFile, 'r');
            $type = $form->get('category')->getData()->getid();
            $this->import->setCategory($form->get('category')->getData());
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
                            $this->blockData = new Data;
                            $this->blockData->setAdr($arrayData[$i]['adr']);
                            $this->blockData->setDatetime($arrayData[$i]['date']);
                            $this->blockData->setStatus($arrayData[$i]['status']);
                            $this->blockData->setDelta1($arrayData[$i]['data'][0]);
                            $this->blockData->setDelta2($arrayData[$i]['data'][2]);
                            $this->blockData->setFilterRatio($arrayData[$i]['data'][4]);
                            $this->blockData->setTemperatureCorrection($arrayData[$i]['data'][6]);
                            $this->blockData->setSlopeTemperatureCorrection($arrayData[$i]['data'][8]);
                            $this->blockData->setRawCo($arrayData[$i]['data'][10]);
                            $this->blockData->setCoCorrection($arrayData[$i]['data'][12]);
                            if (key_exists('alarm', $arrayData[$i])) {
                                $this->blockData->setAlarm($arrayData[$i]['alarm']);
                            }
                            $this->blockData->setImport($this->import);
                            $entityManager->persist($this->blockData);
                            $entityManager->flush();
                        } catch (\Exception $e) {
                            unset($this->blockData);
                        }
                    }
                    $this->addFlash('success', 'L\'importation à bien été effectuée');
                    return $this->redirectToRoute('home');
                }
            } elseif ($type === 2) {
                throw new \Exception('En cours de traitement.');

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