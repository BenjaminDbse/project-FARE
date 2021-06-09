<?php

namespace App\Controller;

use App\Entity\Context;
use App\Entity\ContextData;
use App\Entity\Leading;
use App\Entity\User;
use App\Service\ContextService;
use App\Service\Recorder;
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

/**
 * @Route("/import", name="import_")
 */
class ImportController extends AbstractController
{
    const LOCATION_FILE = '/../../public/imports/';
    const NUMBER_OF_CONTEXT = 4;
    const NUMBER_OF_ELEMENTARY = 15;

    /**
     * @Route("/", name="import", methods={"GET", "POST"})
     * @param Request $request
     * @param Recorder $recorder
     * @param ContextService $contextService
     * @return Response
     */
    public function import(Request $request, Recorder $recorder, ContextService $contextService): Response
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
                        if (count($arrayData[$i]) >= 4) {
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
                        }
                    }
                    $this->addFlash('success', 'L\'importation à bien été effectuée');
                    return $this->redirectToRoute('home');
                }
            } elseif ($type === 2) {
                while (($line = fgetcsv($treatment, 50, ';')) !== false) {
                    $arrayData[] = trim($line[1]);
                }
                fclose($treatment);
                unlink(__DIR__ . self::LOCATION_FILE . $nameFile);
                $lead = $contextService->leadingTreatment($arrayData);
                if (empty($lead['errors'])) {
                    $leading = new Leading;
                    $leading->setEcs($lead[0]);
                    $leading->setEquipment($lead[1]);
                    $leading->setModule($lead[2]);
                    $leading->setLooping($lead[3]);
                    $leading->setAdr($lead[4]);
                    $leading->setZone($lead[5]);
                    $leading->setImport($import);
                    $entityManager->persist($leading);

                    $primary = 11;
                    for ($i = 1; $i <= self::NUMBER_OF_CONTEXT; $i++) {
                        $lead = $contextService->contextTreatment($arrayData, $primary);
                        if (empty($lead['errors']) && empty($arrayData['errors'])) {
                            $context = new Context;
                            $context->setImport($import);
                            $context->setNumber(($i));
                            $context->setAlgo($lead['algo']);
                            $context->setEvalutionCase($lead['caseEvaluation']);
                            $context->setHalfContext($lead['halfContext']);
                            $context->setProductIdentifier($lead['productIdentifier']);
                            $context->setDatetime($lead['date']);
                            $context->setEncrOne($lead['encr1']);
                            $context->setEncrTwo($lead['encr2']);
                            $context->setSlopeSeuil($lead['slopeTemp']);
                            $context->setRatioAlarm($lead['ratio']);
                            $context->setDeltaSeuil($lead['delta2']);
                            $context->setTempAlarm($lead['tempAlarm']);
                            $context->setVelocimeter($lead['velocimeter']);
                            $entityManager->persist($context);
                            $primary += 30;

                            for ($j = 0; $j < self::NUMBER_OF_ELEMENTARY; $j++) {
                                $lead = $contextService->elementaryTreatment($arrayData, $primary);
                                if (empty($lead['errors']) && empty($arrayData['errors'])) {
                                    $contextData = new ContextData;
                                    $contextData->setRatio($lead['ratio']);
                                    $contextData->setDelta1($lead['delta1']);
                                    $contextData->setPulse1($lead['pulse1']);
                                    $contextData->setDelta2($lead['delta2']);
                                    $contextData->setPulse2($lead['pulse2']);
                                    $contextData->setTempRaw($lead['rawTemp']);
                                    $contextData->setTempCorrected($lead['slopeTemp']);
                                    $contextData->setCo($lead['co']);
                                    $contextData->setContext($context);
                                    $entityManager->persist($contextData);
                                    $entityManager->flush();
                                    $primary += 15;
                                } else {
                                    $arrayData['errors'][] = $lead['errors'];
                                }
                            }
                        } else {
                            $arrayData['errors'][] = $lead['errors'];
                        }
                    }
                    if (empty($arrayData['errors'])) {
                        $this->addFlash('success', 'L\'importation à bien été effectuée');
                        return $this->redirectToRoute('home');
                    }
                } else {
                    $arrayData['errors'][] = $lead['errors'];
                }
            }
        }
        return $this->render('import/import.html.twig', [
            'form' => $form->createView(),
            'errors' => $arrayData['errors'] ?? '',
        ]);
    }

    private
    function moveAndNameFile(object $dataFile): string
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
    public
    function delete(Request $request, Import $import): Response
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