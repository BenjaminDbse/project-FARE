<?php

namespace App\Controller;

use App\Entity\ContextData;
use App\Entity\ImportContext;
use App\Form\ContextFilterType;
use App\Repository\AlgoRepository;
use App\Repository\ContextDataRepository;
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
     * @param ImportContext $import
     * @ParamConverter("import", class="App\Entity\ImportContext", options={"mapping": {"id": "id"}})
     * @param ChartBuilderInterface $chartBuilder
     * @param ContextDataRepository $contextDataRepository
     * @param AlgoRepository $algoRepository
     * @param Request $request
     * @return Response
     */
    public function graph(
        ImportContext $import,
        ChartBuilderInterface $chartBuilder,
        ContextDataRepository $contextDataRepository,
        AlgoRepository $algoRepository,
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
        $form = $this->createForm(ContextFilterType::class);
        $form->handleRequest($request);
        foreach ($import->getContexts() as $allContext) {
            $context[] = 'context ' . $allContext->getNumber();
        }
        foreach ($import->getContexts() as $allContext) {
            foreach ($allContext->getContextData() as $data) {
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
        $max = max($ratio);
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => [$max],
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
            'form' => $form->createView(),
        ]);
    }
}