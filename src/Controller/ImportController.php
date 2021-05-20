<?php

namespace App\Controller;

use App\Entity\User;
use DateTime;
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
    const START_TREATMENT = '*********';
    const TRIM_ALARM = " \n\r\t\v\0";
    const NOT_CALCULATED = 60000;
    const ERROR_DETECT = 64609;
    const RESULT_ERROR = 2048;
    const DIVISION_DATA = 10;
    const RATIO_NOT_CALCULATED = 10;
    private Import $import;
    private Data $blockData;
    private int $loopTreatment = 1;
    private int $counter = 0;
    private int $adr;
    private DateTime $date;
    private array $dataClean;
    private array $arrayData;
    private array $data1;
    private array $data2;
    private array $data3;
    private array $data4;
    private array $data5;
    private array $data6;
    private array $data7;


    /**
     * @Route("/", name="import", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function import(Request $request): Response
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
            $this->import->setType('Enregistrement');
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
            while (!feof($treatment)) {
                /* Initialisation des variables qui me servent à boucler sur 3 lignes différentes
                et instantiation de Data  */
                $this->blockData = new Data;
                $line = fgets($treatment);
                if (!(stristr($line, self::START_TREATMENT) || (substr(nl2br($line), 0, 3) == "<br"))) {
                    if ($this->loopTreatment == 1) {
                        $this->firstTreatment($line, $entityManager);
                    } elseif ($this->loopTreatment == 2) {
                        $this->secondTreatment($line);
                    } elseif ($this->loopTreatment == 3) {
                        $this->thirdTreatment($line, $entityManager);
                    }
                }
                $entityManager->flush();
            }
            fclose($treatment);
            unlink(__DIR__ . self::LOCATION_FILE . $nameFile);
            $this->addFlash('success', 'L\'importation à bien été effectuée');
            return $this->redirectToRoute('home');
        }
        return $this->render('import/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function moveAndNameFile(object $dataFile): string
    {
        $nameFile = pathinfo($dataFile->getClientOriginalName(), PATHINFO_FILENAME) . '.txt';
        move_uploaded_file($dataFile->getPathName(), __DIR__ . self::LOCATION_FILE . $nameFile);

        return $nameFile;
    }

    private function firstTreatment($line, $entityManager)
    {
        if (!stristr($line, 'ID_BLOC_ENCR') || !stristr($line, 'BLOC_DATAS')) {
            $date = substr($line, 1, 19);
            $this->adr = intval(substr(strpbrk($line, '='), 1, 3));
            $this->adr = rtrim($this->adr, ", ");
            $date = str_replace('/', '-', $date);
            $this->arrayData[$this->counter]['date'] = $date;
            $date = new Datetime($this->arrayData[$this->counter]['date']);
            $this->arrayData[$this->counter]['adr'] = $this->adr;
            $this->loopTreatment += 1;
            $this->blockData->setDatetime($date);
            $this->blockData->setAdr($this->arrayData[$this->counter]['adr']);
            $this->blockData->setImport($this->import);
            if (stristr($line, 'STATUS_ALARM')) {
                $this->TreatmentAlarm($line, $entityManager);
            }
        } else {
            $this->loopTreatment = 1;
        }
    }

    private function TreatmentAlarm($line, $entityManager)
    {
        $alarm = substr($line, 62, 2);
        $alarm = trim($alarm, self::TRIM_ALARM);
        $this->arrayData[$this->counter]['alarm'] = intval($alarm);
        $this->blockData->setAlarm($this->arrayData[$this->counter]['alarm']);
        $this->blockData->setStatus($this->blockData->getAlarm());

        if (($this->arrayData[$this->counter]['adr'] == $this->adr) && (isset($this->arrayData[$this->counter]['adr']))) {
            $this->saveDataAlarm($entityManager);
            $this->loopTreatment = 1;
            $this->counter += 1;
        }
    }

    private function saveDataAlarm($entityManager)
    {
        $this->blockData->setDelta1($this->data1[$this->arrayData[$this->counter]['adr']]);
        $this->blockData->setDelta2($this->data2[$this->arrayData[$this->counter]['adr']]);
        $this->blockData->setFilterRatio($this->data3[$this->arrayData[$this->counter]['adr']]);
        $this->blockData->setTemperatureCorrection($this->data4[$this->arrayData[$this->counter]['adr']]);
        $this->blockData->setSlopeTemperatureCorrection($this->data5[$this->arrayData[$this->counter]['adr']]);
        $this->blockData->setRawCo($this->data6[$this->arrayData[$this->counter]['adr']]);
        $this->blockData->setCoCorrection($this->data7[$this->arrayData[$this->counter]['adr']]);
        $entityManager->persist($this->blockData);
    }

    private function secondTreatment($line)
    {
        $status = substr($line, -4);
        $status = trim($status);
        $this->arrayData[$this->counter]['status'] = $status;
        $this->loopTreatment += 1;
    }

    private function thirdTreatment($line, $entityManager)
    {
        if (!stristr($line, 'BLOC_DATA') || !stristr($line, 'ID_BLOC_ENCR')) {
            $data = substr($line, 46, 69);
            $data = explode(', ', $data);
            $data[13] = explode(' ', $data[13]);
            $data[13] = $data[13][0];
            $data[13] = trim($data[13], self::TRIM_ALARM);
            if (array_key_exists(14, $data)) {
                unset($data[14]);
            }
            for ($i = 0; $i < count($data); $i++) {
                $data[$i] = intval($data[$i]);
            }
            $this->arrayData[$this->counter]['data'] = str_replace("/", "-", $data);
            if (count($this->arrayData[$this->counter]['data']) == 14) {
                $this->calculateData($this->arrayData[$this->counter]['data']);
            }
            $this->date = new DateTime($this->arrayData[$this->counter]['date']);
            $this->saveData();
            if (isset($this->arrayData[$this->counter]['alarm'])) {
                $this->blockData->setAdr($this->arrayData[$this->counter]['adr']);
                $this->blockData->setDatetime($this->arrayData[$this->counter]['date']);
                $this->blockData->setAlarm($this->arrayData[$this->counter]['alarm']);
            }
            $entityManager->persist($this->blockData);
        } else {
            $this->loopTreatment = 1;
            $this->counter -= 1;
        }
        $this->data1[$this->arrayData[$this->counter]['adr']] = $this->blockData->getDelta1();
        $this->data2[$this->arrayData[$this->counter]['adr']] = $this->blockData->getDelta2();
        $this->data3[$this->arrayData[$this->counter]['adr']] = $this->blockData->getFilterRatio();
        $this->data4[$this->arrayData[$this->counter]['adr']] = $this->blockData->getTemperatureCorrection();
        $this->data5[$this->arrayData[$this->counter]['adr']] = $this->blockData->getSlopeTemperatureCorrection();
        $this->data6[$this->arrayData[$this->counter]['adr']] = $this->blockData->getRawCo();
        $this->data7[$this->arrayData[$this->counter]['adr']] = $this->blockData->getCoCorrection();
        if (!empty($this->dataClean)) {
            $this->dataClean = [];
        }
        $this->loopTreatment = 1;
        $this->counter += 1;

    }

    private function calculateData(array $arrayData)
    {
        for ($j = 0; $j < count($arrayData); $j += 2) {
            $this->dataClean[$j] = ($arrayData[$j] + (256 * $arrayData[$j + 1]));
        }
        if ($this->dataClean[2] > self::ERROR_DETECT) {
            $this->dataClean[2] = self::RESULT_ERROR;
        }
        if ($this->dataClean[4] > self::NOT_CALCULATED) {
            $this->dataClean[4] = self::RATIO_NOT_CALCULATED;
        }
        if ($this->dataClean[4] != self::RATIO_NOT_CALCULATED) {
            $this->dataClean[4] = $this->dataClean[4] / self::DIVISION_DATA;
        }
        for ($i = 6; $i < count($this->dataClean) * 2; $i += 2) {
            if ($this->dataClean[$i] > self::ERROR_DETECT) {
                $this->dataClean[$i] = 0;
            }
        }
    }

    private function saveData()
    {
        $this->blockData->setDatetime($this->date);
        $this->blockData->setAdr($this->arrayData[$this->counter]['adr']);
        $this->blockData->setStatus(intval($this->arrayData[$this->counter]['status']));
        $this->blockData->setDelta1(($this->dataClean[0] / self::DIVISION_DATA));
        $this->blockData->setDelta2(($this->dataClean[2] / self::DIVISION_DATA));
        $this->blockData->setFilterRatio(($this->dataClean[4]));
        $this->blockData->setTemperatureCorrection(($this->dataClean[6] / self::DIVISION_DATA));
        $this->blockData->setSlopeTemperatureCorrection(($this->dataClean[8] / self::DIVISION_DATA));
        $this->blockData->setRawCo(($this->dataClean[10]));
        $this->blockData->setCoCorrection(($this->dataClean[12]));
        $this->blockData->setImport($this->import);
    }

    /**
     * @Route("/{id}", name="delete", methods={"DELETE"})
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
        }
        $this->addFlash('danger', 'L\'Archive à bien été supprimée');
        return $this->redirectToRoute('archive');
    }
}