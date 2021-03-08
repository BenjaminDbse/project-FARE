<?php

namespace App\Controller;

use App\Entity\Import;
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
     * @Route("/graphique/{id}", name="graph", methods={"GET"})
     * @param Import $import
     * @param ChartBuilderInterface $chartBuilder
     * @return Response
     */
    public function graph(Import $import,ChartBuilderInterface $chartBuilder): Response
    {
        $delta1 = [];
        $delta2 = [];
        $ratioFilter = [];
        $temperatureCorrection = [];
        $slopeTemperatureCorrection = [];
        $rawCo = [];
        $coCorrection = [];
        $adr= [];
        $status = [];
        $datetime = [];

        foreach ($import->getData() as $data){
            $delta1[] = $data->getDelta1();
            $delta2[] = $data->getDelta2();
            $ratioFilter[] = $data->getFilterRatio();
            $slopeTemperatureCorrection[] = $data->getSlopeTemperatureCorrection();
            $rawCo[] = $data->getRawCo();
            $coCorrection[] = $data->getCoCorrection();
            $adr[] = $data->getAdr();
            $status[] = $data->getStatus();
            $temperatureCorrection[] = $data->getTemperatureCorrection();
            $datetime[] = date_format($data->getDatetime(),'H:i:s');
        }
        $max1 = max($delta1);
        $max2 = max($delta2);
        $max3 = max($ratioFilter);
        $max4 = max($temperatureCorrection);
        $max5 = max($slopeTemperatureCorrection);
        $max6 = max($rawCo);
        $max7 = max($coCorrection);
        $max = max($max1,$max2,$max3,$max4,$max5,$max6,$max7);

        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $datetime,
            'backgroundColor' => '#FFFFFF',
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
                    'yAxisID'=> 'left-y-axis',
                ],
                [
                    'label' => 'Correction de CO',
                    'backgroundColor' => 'rgba(255, 99, 132,0)',
                    'borderColor' => 'rgb(195, 38, 0)',
                    'data' => $coCorrection,
                    'yAxisID'=> 'left-y-axis',
                ],
                [
                    'label' => 'Ratio filtré',
                    'backgroundColor' => 'rgba(255, 99, 132,0)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $ratioFilter,
                    'yAxisID'=> 'right-y-axis',
                ],
            ],
        ]);

        $chart->setOptions([
            'scales' => [
                'yAxes' => [
                    ['ticks' => ['suggestedMin' => 0, 'suggestedMax' => $max,], 'position'=> 'left', 'id' => 'left-y-axis'],
                    ['ticks' => ['suggestedMin'=> min($ratioFilter), 'suggestedMax' => max($ratioFilter)], 'position'=> 'right', 'id' => 'right-y-axis'],
                ],
            ],
        ]);
        return $this->render('graph/graph.html.twig', [
            "chart" => $chart,
            "import"=> $import,
        ]);
    }
}
