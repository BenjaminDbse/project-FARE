<?php

namespace App\Controller;

use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ImportType;
use App\Entity\Import;
use App\Entity\Data;
use App\Services\Slugify;

/**
 * @Route("/import", name="import_")
 */
class ImportController extends AbstractController
{
    /**
     * @Route("/", name="import", methods={"GET", "POST"})
     * @param Request $request
     * @param Slugify $slugify
     * @return Response
     * @throws Exception
     */
    public function import(Request $request, Slugify $slugify): Response
    {
        $import = new import;
        $form = $this->createForm(ImportType::class, $import);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dataFile = $form->get('file')->getData();
            $slug = $slugify->generate($import->getTitle());
            $import->setSlugify($slug);
            $entityManager = $this->getDoctrine()->getManager();
            $import->setTitle($form->get('title')->getData());
            $import->setDatetime(new DateTime('now'));
            $entityManager->persist($import);
            $entityManager->flush();
            if (!empty($dataFile)) {
                $name = pathinfo($dataFile->getClientOriginalName(), PATHINFO_FILENAME);
                move_uploaded_file(
                    $dataFile->getPathName(),
                    __DIR__ . '/../../public/imports/' . $name . '.txt'
                );
                $treatment = fopen(__DIR__ . '/../../public/imports/' . $name . '.txt', 'r');
                $data1 = [];
                $data2 = [];
                $data3 = [];
                $data4 = [];
                $data5 = [];
                $data6 = [];
                $data7 = [];
                $array = [];
                $numbOfThree = 1;
                $count = 0;
                while (!feof($treatment)) {
                    $blockData = new Data;
                    $line = fgets($treatment);

                    if (!(stristr($line, '*********') || (substr(nl2br($line),0,3) == "<br"))) {
                        if ($numbOfThree == 1) {
                            if (stristr($line,'STATUS_ALARM') && !stristr($line, 'ID_BLOC_ENCR')) {
                                $date = substr($line, 1, 19);
                                $adr = substr(strpbrk($line, '='), 1, 3);
                                $adr = rtrim($adr, ", ");
                                $alarm = substr($line,62,2);
                                $alarm = trim($alarm," \n\r\t\v\0");
                                $array[$count]['date'] = $date;
                                $array[$count]['adr'] = $adr;
                                $array[$count]['alarm'] = $alarm;
                                $date = str_replace("/", "-", $array[$count]['date']);
                                $date = new DateTime($date);
                                $blockData->setDatetime($date);
                                $blockData->setAdr($array[$count]['adr']);
                                $blockData->setAlarm($array[$count]['alarm']);
                                $blockData->setImport($import);
                                $blockData->setStatus($blockData->getAlarm());
                                if (($array[$count]['adr'] == $adr) && (isset($array[$count]['adr']))) {
                                    $blockData->setDelta1($data1[$array[$count]['adr']]);
                                    $blockData->setDelta2($data2[$array[$count]['adr']]);
                                    $blockData->setFilterRatio($data3[$array[$count]['adr']]);
                                    $blockData->setTemperatureCorrection($data4[$array[$count]['adr']]);
                                    $blockData->setSlopeTemperatureCorrection($data5[$array[$count]['adr']]);
                                    $blockData->setRawCo($data6[$array[$count]['adr']]);
                                    $blockData->setCoCorrection($data7[$array[$count]['adr']]);
                                }
                                $entityManager->persist($blockData);
                                $numbOfThree = 1 ;
                                $count += 1;
                            } else {
                                $date = substr($line, 1, 19);
                                $adr = substr(strpbrk($line, '='), 1, 3);
                                $adr = rtrim($adr, ", ");
                                $array[$count]['date'] = $date;
                                $array[$count]['adr'] = $adr;
                                $numbOfThree += 1;
                            }
                        } elseif ($numbOfThree == 2) {
                            $status = substr($line, -4);
                            $status = trim($status);
                            $array[$count]['status'] = $status;
                            $numbOfThree += 1;
                        } elseif (($numbOfThree == 3) && !stristr($line, 'BLOC_DATAS')) {
                            $data = substr($line, 46, 69);
                            $data = explode(', ', $data);
                            $data[13] = explode(' ', $data[13]);
                            $data[13] = $data[13][0];
                            $data[13] = trim($data[13], " \n\r\t\v\0");
                            if (key_exists(14, $data)) {
                                unset($data[14]);
                            }
                            $array[$count]['data'] = $data;
                            $numbOfThree += 1;
                            if (count($array[$count]['data']) == 14) {
                                for ($j = 0; $j < count($array[$count]['data']); $j += 2) {
                                    $dataClean[$j] = ($array[$count]['data'][$j] + (256 * $array[$count]['data'][$j + 1]));
                                }
                            }
                            if ($dataClean[2] > 64609) {
                                $dataClean[2] = 2048;
                            }
                            if ($dataClean[4] > 60000) {
                                $dataClean[4] = 10;
                            }
                            if ($dataClean[4] != 10) {
                                $dataClean[4] = $dataClean[4] /10;
                            }
                            $date = str_replace("/", "-", $array[$count]['date']);
                            $date = new DateTime($date);
                            $blockData->setDatetime($date);
                            $blockData->setAdr($array[$count]['adr']);
                            $blockData->setStatus(intval($array[$count]['status']));
                            $blockData->setDelta1(($dataClean[0]/10));
                            $blockData->setDelta2(($dataClean[2]/10));
                            $blockData->setFilterRatio(($dataClean[4]));
                            $blockData->setTemperatureCorrection(($dataClean[6]/10));
                            $blockData->setSlopeTemperatureCorrection(($dataClean[8]));
                            $blockData->setRawCo(($dataClean[10]));
                            $blockData->setCoCorrection(($dataClean[12]));
                            $blockData->setImport($import);
                            if (isset($array[$count]['alarm'])) {
                                $blockData->setAdr($array[$count]['adr']);
                                $blockData->setDatetime($array[$count]['date']);
                                $blockData->setAlarm($array[$count]['alarm']);
                            }
                            $entityManager->persist($blockData);
                        } else {
                            $numbOfThree = 1;
                        }
                            if ($numbOfThree == 4) {

                                $data1[$array[$count]['adr']] = $blockData->getDelta1();
                                $data2[$array[$count]['adr']] = $blockData->getDelta2();
                                $data3[$array[$count]['adr']] = $blockData->getFilterRatio();
                                $data4[$array[$count]['adr']] = $blockData->getTemperatureCorrection();
                                $data5[$array[$count]['adr']] = $blockData->getSlopeTemperatureCorrection();
                                $data6[$array[$count]['adr']] = $blockData->getRawCo();
                                $data7[$array[$count]['adr']] = $blockData->getCoCorrection();
                                if (!empty($dataClean)) {
                                    $dataClean = [];
                                }
                                $numbOfThree = 1;
                                $count += 1;
                            }
                    }
                    $entityManager->flush();
                }
                fclose($treatment);
                unlink(__DIR__ . '/../../public/imports/' . $name . '.txt');
                $this->addFlash('success', 'L\'importation à bien été effectuée');
                return $this->redirectToRoute('home');
            }
        }
        return $this->render('import/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
