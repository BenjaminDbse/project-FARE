<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ImportType;
use App\Entity\Import;

class ImportController extends AbstractController
{
    /**
     * @Route("/importer-un-enregistrement", name="import", methods={"GET", "POST"})
     * @param Request $request
     * @return Response
     */
    public function import(Request $request): Response
    {
        $import = new import;
        $form = $this->createForm(ImportType::class, $import);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dataFile = $form->get('file')->getData();
            if (!empty($dataFile)) {
                $name = pathinfo($dataFile->getClientOriginalName(), PATHINFO_FILENAME);
                move_uploaded_file(
                    $dataFile->getPathName(),
                    __DIR__ . '/../../public/imports/' . $name . '.txt'
                );
                $treatment = fopen('/home/ubuntu/Documents/TCA/public/imports/' . $name . '.txt', 'r');
                $array = [];
                $numbOfThree = 1;
                $count=0;
                while (!feof($treatment)) {
                    $line = fgets($treatment);
                    if (!stristr($line, '*********')) {
                        if ($numbOfThree == 1) {
                            $date = substr($line, 1, 19);
                            $adr = substr(strpbrk($line, '='), 1, 1);
                            $array[$count]['date'] = $date;
                            $array[$count]['adr'] = $adr;
                            $numbOfThree += 1;
                        } elseif ($numbOfThree == 2) {
                            $status = substr($line, -3);
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
                            for ($i = 0; $i < count($array[$count]); $i++) {
                                for ($j = 0; $j < count($array[$count]['data']); $j += 2) {
                                    $dataClean[$i] = $array[$count]['data'][$j] + $array[$count]['data'][$j +1];
                                }
                            }
                        }
                        if ($numbOfThree == 4) {
                            $numbOfThree = 1;
                            $count+=1;
                        }
                    }
                }
                fclose($treatment);
            }
        }
        return $this->render('import/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
