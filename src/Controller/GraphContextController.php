<?php

namespace App\Controller;

use App\Entity\Context;
use App\Entity\ContextData;
use App\Entity\Import;
use App\Entity\Leading;
use App\Form\ContextFilterType;
use App\Repository\AlgoRepository;
use App\Repository\ContextDataRepository;
use App\Repository\ContextRepository;
use App\Repository\LeadingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * @Route("/graphique-context", name="graph_")
 */
class GraphContextController extends AbstractController
{
    /**
     * @Route("/{id}", name="context", methods={"GET","POST"})
     * @param Import $import
     * @param ChartBuilderInterface $chartBuilder
     * @param ContextDataRepository $contextDataRepository
     * @param AlgoRepository $algoRepository
     * @param LeadingRepository $leading
     * @param ContextRepository $contextRepository
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function graph(
        Import $import,
        ChartBuilderInterface $chartBuilder,
        ContextDataRepository $contextDataRepository,
        AlgoRepository $algoRepository,
        LeadingRepository $leading,
        ContextRepository $contextRepository,
        Request $request
    ): Response
    {
        $context = [];
        $ratio = [];
        $pulse1 = [];
        $pulse2 = [];
        $delta1 = [];
        $delta2 = [];
        $tempRaw = [];
        $tempCorrected = [];
        $co = [];
        $contextChoiceSelected = 0;
        $contextInfo = [];
        $timeStamps = [];
        $date = [];

        foreach ($import->getContexts() as $allContext) {
            $context[] = $allContext->getNumber();
        }
        if (isset($_POST['context'])) {
            $contextChoiceSelected = $_POST['context'];
            $contextChoice = $contextRepository->findBy(['number' => $contextChoiceSelected, 'import' => $import->getId()]);
            foreach ($contextChoice as $data) {
                $contextInfo['id'] = $data->getId();
                $contextInfo['date'] = $data->getDatetime();
                $contextInfo['algo'] = $data->getAlgo();
                $contextInfo['evaluation'] = $data->getEvalutionCase();
                $contextInfo['halfcontext'] = $data->getHalfContext();
                $contextInfo['identifiant'] = $data->getProductIdentifier();
                $contextInfo['encr1'] = $data->getEncrOne();
                $contextInfo['encr2'] = $data->getEncrTwo();
                $contextInfo['slope'] = $data->getSlopeSeuil();
                $contextInfo['ratio'] = $data->getRatioAlarm() ?? null;
                $contextInfo['delta'] = $data->getDeltaSeuil() ?? null;
                $contextInfo['temp'] = $data->getTempAlarm() ?? null;
                $contextInfo['velocimeter'] = $data->getVelocimeter() ?? null;
            }
            $timeStamp = $contextInfo['date']->getTimestamp();
            for ($i = 0; $i <= 28; $i += 2) {
                $timeStamps[$i] = $timeStamp + $i;
                $timeStamps[$i] = getdate($timeStamps[$i]);
                $timeStamps[$i] =
                    $timeStamps[$i]['year'] . '-' .
                    $timeStamps[$i]['mon'] . '-' .
                    $timeStamps[$i]['mday'] . ' ' .
                    $timeStamps[$i]['hours'] . ':' .
                    $timeStamps[$i]['minutes'] . ':' .
                    $timeStamps[$i]['seconds'];
                $timeStamps[$i] = new \DateTime($timeStamps[$i]);
                $timeStamps[$i] = date_format($timeStamps[$i],'d-m-Y  /  H:i:s');
                $date[] = $timeStamps[$i];
            }
            $contextData = $contextDataRepository->findBy(['context' => $contextInfo['id']]);
            foreach ($contextData as $data) {
                $ratio[] = $data->getRatio();
                $pulse1[] = $data->getPulse1();
                $pulse2[] = $data->getPulse2();
                $delta1[] = $data->getDelta1();
                $delta2[] = $data->getDelta2();
                $tempRaw[] = $data->getTempRaw();
                $tempCorrected[] = $data->getTempCorrected();
                $co[] = $data->getCo();
            }
        }
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $date,
            'datasets' => [
                [
                    'label' => 'Delta 1',
                    'backgroundColor' => 'rgba( 233, 117, 0 ,0)',
                    'borderColor' => 'rgb(233, 117, 0)',
                    'data' => $delta1,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Delta 2',
                    'backgroundColor' => 'rgba(2, 2, 200,0)',
                    'borderColor' => 'rgb(2, 2, 200)',
                    'data' => $delta2,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Température brut',
                    'backgroundColor' => 'rgba(255, 99, 132,0)',
                    'borderColor' => 'rgb( 203, 11, 198)',
                    'data' => $tempRaw,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Température corrigée',
                    'backgroundColor' => 'rgba(255, 99, 132,0)',
                    'borderColor' => 'rgb( 203, 11, 198)',
                    'data' => $tempCorrected,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'CO',
                    'backgroundColor' => 'rgba(255,99,132,0)',
                    'borderColor' => 'rgb( 128, 128, 128)',
                    'data' => $co,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Pulse différentiel channel 1',
                    'backgroundColor' => 'rgba(91, 0, 0,0)',
                    'borderColor' => 'rgb(91, 0, 0)',
                    'data' => $pulse1,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Pulse différentiel channel 2',
                    'backgroundColor' => 'rgba(19,182,8,0)',
                    'borderColor' => 'rgb(19,182,8)',
                    'data' => $pulse2,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Ratio filtré',
                    'backgroundColor' => 'rgba(255, 255, 255,0)',
                    'borderColor' => 'rgb(83, 108, 0)',
                    'data' => $ratio,
                    'yAxisID' => 'right-y-axis',
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'yAxes' =>
                    [
                        ['ticks' => ['suggestedMin' => 0, 'suggestedMax' => 10], 'position' => 'left', 'id' => 'left-y-axis',],
                        ['ticks' => ['suggestedMin' => 0, 'suggestedMax' => 12], 'position' => 'right', 'id' => 'right-y-axis'],
                    ],
            ],
            'elements' => [
                'line' => ['tension' => 0],
            ],
            "maintainAspectRatio" => false,
        ]);
        return $this->render('graph/graphContext.html.twig', [
            "chart" => $chart,
            "import" => $import,
            "contexts" => $import->getContexts(),
            'contextChoices' => $context,
            'contextChoiceSelected' => $contextChoiceSelected,
            'leadings' => $leading->findBy(['import' => $import]),
            'contextInfo' => $contextInfo ?? '',
        ]);
    }
}