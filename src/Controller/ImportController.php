<?php

namespace App\Controller;

use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
    /**
     * @Route("/importer-un-enregistrement", name="import", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     * @throws Exception|Exception
     */
    public function import(Request $request): Response
    {
        $import = new import;
        $form = $this->createForm(ImportType::class, $import);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dataFile = $form->get('file')->getData();
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
                $treatment = fopen('/home/ben/Bureau/FARE/project-FARE/public/imports/' . $name . '.txt', 'r');
                $array = [];
                $numbOfThree = 1;
                $count=0;
                while (!feof($treatment)) {
                    $blockData = new Data;
                    $line = fgets($treatment);
                    if (!stristr($line, '*********')) {
                        if ($numbOfThree == 1) {
                            $date = substr($line, 1, 19);
                            $adr = substr(strpbrk($line, '='), 1,3 );
                            $adr = rtrim($adr, ", ");
                            $array[$count]['date'] = $date;
                            $array[$count]['adr'] = $adr;
                            $numbOfThree += 1;
                        } elseif ($numbOfThree == 2) {
                            $status = substr($line, -4);
                            $status = trim($status);
                            $array[$count]['status'] = $status;
                            $numbOfThree += 1;
                        } elseif ($numbOfThree == 3) {
                            $data = substr($line, 46, 69);
                            $data = explode(', ', $data);
                            $clean = preg_split("/[\s]+/", $data[13]);
                            $data[13] = $clean[0];
                            if (key_exists(14, $data)) {
                                unset($data[14]);
                            }
                            $array[$count]['data'] = $data;
                            $numbOfThree += 1;
                            for ($j = 0; $j < count($array[$count]['data']); $j += 2) {
                                $dataClean[$j] = (($array[$count]['data'][$j] + 256) * $array[$count]['data'][$j +1]);
                            }
                            if ($dataClean[2] > 64609) {
                                $dataClean[2] = 2048;
                            }
                            if ($dataClean[4] > 60000) {
                                $dataClean[4] = 10;
                            }
                            $date = str_replace("/","-",$array[$count]['date']);
                            $date = new DateTime($date);
                            $blockData->setDatetime($date);
                            $blockData->setAdr($array[$count]['adr']);
                            $blockData->setStatus($array[$count]['status']);
                            $blockData->setDelta1($dataClean[0]);
                            $blockData->setDelta2($dataClean[2]);
                            $blockData->setFilterRatio($dataClean[4]);
                            $blockData->setTemperatureCorrection($dataClean[6]);
                            $blockData->setSlopeTemperatureCorrection($dataClean[8]);
                            $blockData->setRawCo($dataClean[10]);
                            $blockData->setCoCorrection($dataClean[12]);
                            $blockData->setImport($import);
                            $entityManager->persist($blockData);
                            if (!empty($dataClean)) {
                                $dataClean = [];
                            }
                        }
                        if ($numbOfThree == 4) {
                            $numbOfThree = 1;
                            $count+=1;
                        }
                    }
                }
                $entityManager->flush();
                fclose($treatment);
                unlink('/home/ben/Bureau/FARE/project-FARE/public/imports/' . $name . '.txt');
                $this->addFlash('success','L\'importation à bien été effectuée');
                return $this->redirectToRoute('home');
            }
        }
        return $this->render('import/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"})
     * @param Import $import
     * @return Response
     */
    public function show(Import $import): Response
    {
        return $this->render('import/show.html.twig', [
            'import' => $import,
        ]);
    }
}
