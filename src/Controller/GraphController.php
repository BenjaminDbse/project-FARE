<?php

namespace App\Controller;

use App\Entity\Import;
use App\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;


/**
 * @Route("/graph")
 */
class GraphController extends AbstractController
{
    /**
     * @Route("/graphique/{id}", name="graph", methods={"GET","POST"})
     * @param Import $import
     * @param ChartBuilderInterface $chartBuilder
     * @param DataRepository $dataRepository
     * @return Response
     */
    public function graph(Import $import,ChartBuilderInterface $chartBuilder, DataRepository $dataRepository): Response
    {
            $delta1 = [];
            $delta2 = [];
            $ratioFilter = [];
            $temperatureCorrection = [];
            $slopeTemperatureCorrection = [];
            $rawCo = [];
            $coCorrection = [];
            $adr = [];
            $status = [];
            $datetime = [];
            $alarm = [];
            $max = [];
            $choiceUser = null;
        foreach ($import->getData() as $data){
            $adr[] = $data->getAdr();

        }
        $adr = array_unique($adr);

            if (isset($_POST['adr'])) {
                $choiceUser = $_POST['adr'];
                $dataFilter = $dataRepository->findByLikeAdr($import->getId(), $choiceUser);

                foreach ($dataFilter as $data) {
                    $delta1[] = $data->getDelta1();
                    $delta2[] = $data->getDelta2();
                    $ratioFilter[] = $data->getFilterRatio();
                    $slopeTemperatureCorrection[] = $data->getSlopeTemperatureCorrection();
                    $rawCo[] = $data->getRawCo();
                    $coCorrection[] = $data->getCoCorrection();
                    $status[] = $data->getStatus();
                    $temperatureCorrection[] = $data->getTemperatureCorrection();
                    $datetime[] = date_format($data->getDatetime(),'H:i:s');
                    $alarm[] = $data->getAlarm();
                }
                $max = [
                    max($delta1),
                    max($delta2),
                    max($slopeTemperatureCorrection),
                    max($rawCo),
                    max($temperatureCorrection),
                ];
            }
            for ($i =0 ; $i< count($alarm) ; $i++) {
                if ($alarm[$i] >=  1) {
                    $alarm[$i -1] = 0;
                    $alarm[$i] += max($max);
                    $alarm[$i +1] = 0;
                }
            }
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $datetime,
            'datasets' => [
                [
                    'label' => 'Delta 1',
                    'backgroundColor' => 'rgba( 230, 146, 0 ,0)',
                    'borderColor' => 'rgb(230, 146, 0)',
                    'data' => $delta1,
                    'yAxisID'=> 'left-y-axis',
                ],
                [
                    'label' => 'Delta 2',
                    'backgroundColor' => 'rgba(1, 228, 225,0)',
                    'borderColor' => 'rgb(1, 228, 225)',
                    'data' => $delta2,
                    'yAxisID'=> 'left-y-axis',
                ],
                [
                    'label' => 'Alarme',
                    'backgroundColor' => 'rgba(1, 228, 225,0)',
                    'borderColor' => 'rgb(255, 0, 0)',
                    'data' => $alarm,
                    'yAxisID'=> 'left-y-axis',
                ],
                [
                    'label' => 'Correction de température',
                    'backgroundColor' => 'rgba(255, 99, 132,0)',
                    'borderColor' => 'rgb( 203, 11, 198)',
                    'data' => $temperatureCorrection,
                    'yAxisID'=> 'left-y-axis',
                ],
                [
                    'label' => 'Correction de pente de température',
                    'backgroundColor' => 'rgba(19,182,8,0)',
                    'borderColor' => 'rgb(19,182,8)',
                    'data' => $slopeTemperatureCorrection,
                    'yAxisID'=> 'left-y-axis',
                ],
                [
                    'label' => 'CO Brut',
                    'backgroundColor' => 'rgba(255, 99, 132,0)',
                    'borderColor' => 'rgb( 128, 128, 128)',
                    'data' => $rawCo,
                    'yAxisID'=> 'right-y-axis',
                ],
                [
                    'label' => 'CO Corrigé',
                    'backgroundColor' => 'rgba(255, 99, 132,0)',
                    'borderColor' => 'rgb(195, 38, 0)',
                    'data' => $coCorrection,
                    'yAxisID'=> 'right-y-axis',
                ],
                [
                    'label' => 'Ratio filtré',
                    'backgroundColor' => 'rgba(255, 255, 255,0)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $ratioFilter,
                    'yAxisID'=> 'right-y-axis',
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'yAxes' => [
                    ['ticks' => ['suggestedMin' => 0, 'suggestedMax' => 10,], 'position'=> 'left', 'id' => 'left-y-axis',],
                    ['ticks' => ['suggestedMin'=> 0, 'suggestedMax' => 12], 'position'=> 'right', 'id' => 'right-y-axis'],
                ],
            ],
        ]);
        return $this->render('graph/graph.html.twig', [
            "chart" => $chart,
            "import"=> $import,
            'adrs' => $adr,
        ]);
    }
}
