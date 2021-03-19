<?php

namespace App\Controller;

use App\Entity\Import;
use App\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
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
     * @param Request $request
     * @return Response
     */
    public function graph(Import $import, ChartBuilderInterface $chartBuilder, DataRepository $dataRepository, Request $request): Response
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
        $filterAdr = null;
        $condition = [];
        foreach ($import->getData() as $data) {
            $adr[] = $data->getAdr();
        }
        $adr = array_unique($adr);

        if (!empty($_POST['adr'])) {
            $filterAdr = $request->request->all();
            $filterAdr = $filterAdr['adr'];
            $session = $request->getSession();
            $session->set('adr', $filterAdr);

            $dataFilter = $dataRepository->findByLikeAdr($import->getId(), $filterAdr);
            include __DIR__ . '/../include/resultToRequest.php';
        }
        if (isset($_POST['date']) && !empty($_POST['limit'])) {
            $session = $request->getSession()->all();
            $filterAdr = $session['adr'];
            $userChoiceDate = explode("/", $_POST['date']);
            for ($i = 0; $i < count($userChoiceDate); $i++) {
                $userChoiceDate[$i] = trim($userChoiceDate[$i], ' ');
            }
            $userChoiceDate[0] = explode("-", $userChoiceDate[0]);
            $userChoiceDate[0] = array_reverse($userChoiceDate[0]);
            $userChoiceDate [0] = join("-", $userChoiceDate [0]);
            $userChoiceDate = join(' ', $userChoiceDate);
            $userChoiceLimit = $_POST['limit'];
            $dataFilter = $dataRepository->findByDateToLimit($import->getId(), $filterAdr, $userChoiceDate, $userChoiceLimit);
            include __DIR__ . '/../include/resultToRequest.php';
        }
        for ($i = 0; $i < count($alarm); $i++) {
            if ($alarm[$i] >= 1) {
                $alarm[$i - 1] = 0;
                $alarm[$i] = max($max) + 50 ;
                $alarm[$i + 1] = 0;
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
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Delta 2',
                    'backgroundColor' => 'rgba(1, 228, 225,0)',
                    'borderColor' => 'rgb(1, 228, 225)',
                    'data' => $delta2,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Alarme',
                    'backgroundColor' => 'rgba(255, 0, 0,0.1)',
                    'borderColor' => 'rgb(255, 0, 0)',
                    'data' => $alarm,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Température corrigée',
                    'backgroundColor' => 'rgba(255, 99, 132,0)',
                    'borderColor' => 'rgb( 203, 11, 198)',
                    'data' => $temperatureCorrection,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Pente de température',
                    'backgroundColor' => 'rgba(19,182,8,0)',
                    'borderColor' => 'rgb(19,182,8)',
                    'data' => $slopeTemperatureCorrection,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'CO Brut',
                    'backgroundColor' => 'rgba(255, 99, 132,0)',
                    'borderColor' => 'rgb( 128, 128, 128)',
                    'data' => $rawCo,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'CO Corrigé',
                    'backgroundColor' => 'rgba(255, 99, 132,0)',
                    'borderColor' => 'rgb(195, 38, 0)',
                    'data' => $coCorrection,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Ratio filtré',
                    'backgroundColor' => 'rgba(255, 255, 255,0)',
                    'borderColor' => 'rgb(228, 211, 0)',
                    'data' => $ratioFilter,
                    'yAxisID' => 'right-y-axis',
                ],
            ],
        ]);
        $chart->setOptions([
            'scales' => [
                'yAxes' => [
                    ['ticks' => ['suggestedMin' => 0, 'suggestedMax' => 10,], 'position' => 'left', 'id' => 'left-y-axis',],
                    ['ticks' => ['suggestedMin' => 0, 'suggestedMax' => 12], 'position' => 'right', 'id' => 'right-y-axis'],
                ],
            ],
            'elements' => [
                'line' => ['tension' => 0 ],
            ]
        ]);
        return $this->render('graph/graph.html.twig', [
            "chart" => $chart,
            "import" => $import,
            'adrs' => $adr,
            'adrChoice' => $filterAdr,
            'dates' => $datetime,
            'status' => $status,
            'conditions' =>$condition,
        ]);
    }
}
