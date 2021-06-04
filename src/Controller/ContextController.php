<?php

namespace App\Controller;

use App\Entity\Context;
use App\Entity\ContextData;
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
    const NUMBER_OF_CONTEXT = 2;
    const NUMBER_OF_ELEMENTARY = 15;
    private Context $context;
    private ContextData $data;
    private string $nameFile;
    private array $arrayData = [];
    private int $counter = 0;
    private int $loop = 1;
    private int $primary = 0;
    private int $lasted = 0;

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
        $this->import = new Import;
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
            $this->moveAndNameFile($dataFile);
            $treatment = fopen(__DIR__ . self::LOCATION_FILE . $this->nameFile, 'r');
            while (!feof($treatment)) {
                $this->firstTreatment($treatment);
            }
            for ($i = 0 ; $i < 6 ; $i++) {

            }
            for ($i = 0 ; $i < self::NUMBER_OF_CONTEXT; $i++) {
                $this->saveContext($entityManager);
                $this->loop++;
                for ($j = 0 ; $j < self::NUMBER_OF_ELEMENTARY ; $j++) {
                    $this->saveData($entityManager);
                    $entityManager->flush();
                }
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
        $this->nameFile = pathinfo($dataFile->getClientOriginalName(), PATHINFO_FILENAME) . '.txt';
        move_uploaded_file($dataFile->getPathName(), __DIR__ . self::LOCATION_FILE . $this->nameFile);
    }

    private function firstTreatment($treatment)
    {
        while (($line = fgetcsv($treatment,100,';')) !== false) {
            $this->arrayData[] = trim($line[1]);
        }
    }

    private function saveContext(\Doctrine\Persistence\ObjectManager $entityManager)
    {
        $this->context = new Context;
        $this->context->setImport($this->import);
        $this->context->setAdr($this->arrayData[$this->primary]);
        $this->context->setChoiceOne($this->arrayData[$this->primary + 1]);
        $this->context->setChoiceTwo($this->arrayData[$this->primary + 2]);
        $this->context->setChoiceThree($this->arrayData[$this->primary + 3]);
        $this->context->setChoiceFour($this->arrayData[$this->primary + 4]);
        $this->context->setAlgo($this->arrayData[$this->primary + 6]);
        $this->context->setEvalutionCase($this->arrayData[$this->primary + 7]);
        $this->context->setHalfContext($this->arrayData[$this->primary + 8]);
        $this->context->setProductIdentifier($this->arrayData[$this->primary + 9]);
        $this->context->setDatetime(new DateTime($this->arrayData[$this->primary + 10]));
        $this->context->setEncrOne($this->arrayData[$this->primary + 11]);
        $this->context->setEncrTwo($this->arrayData[$this->primary + 12]);
        $this->context->setRatioAlarm($this->arrayData[$this->primary + 13]);
        $this->context->setDeltaSeuil($this->arrayData[$this->primary + 14]);
        $this->context->setTempAlarm($this->arrayData[$this->primary + 15]);
        $this->context->setVelocimeter($this->arrayData[$this->primary + 16]);
        $this->context->setSlopeSeuil($this->arrayData[$this->primary + 17]);
        $this->context->setNumber($this->loop);

        $entityManager->persist($this->context);
        $this->primary += 18;
    }

    private function saveData(\Doctrine\Persistence\ObjectManager $entityManager)
    {
        $data = [];

        $this->lasted = ($this->primary + 8);
            for ($j = 0 ; $j < ($this->lasted - $this->primary) ; $j++) {
                $data[] = number_format((float)$this->arrayData[$this->primary + $j],1);
            }

            $this->data = new ContextData;
            $this->data->setRatio($data[0]);
            $this->data->setDelta1($data[1]);
            $this->data->setPulse1($data[2]);
            $this->data->setDelta2($data[3]);
            $this->data->setPulse2($data[4]);
            $this->data->setTempRaw($data[5]);
            $this->data->setTempCorrected($data[6]);
            $this->data->setCo($data[7]);
            $this->data->setContext($this->context);
            $this->primary += 8;
            $entityManager->persist($this->data);
    }
}
