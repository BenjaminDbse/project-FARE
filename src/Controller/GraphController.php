<?php

namespace App\Controller;

use App\Entity\Import;
use App\Repository\AlgoRepository;
use App\Repository\DataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

/**
 * @Route("/graphique")
 */
class GraphController extends AbstractController
{
    /**
     * @Route("/{id}", name="graph", methods={"GET","POST"})
     * @param Import $import
     * @param ChartBuilderInterface $chartBuilder
     * @param DataRepository $dataRepository
     * @param AlgoRepository $algoRepository
     * @param Request $request
     * @return Response
     */
    public function graph(
        Import $import,
        ChartBuilderInterface $chartBuilder,
        DataRepository $dataRepository,
        AlgoRepository $algoRepository,
        Request $request
    ): Response

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
        $filterAdr = null;
        $condition = [];
        $resultAlgo = [];
        $dataFilter = [];
        $algoName = 'Algo non défini';

        foreach ($import->getData() as $data) {
            $adr[] = $data->getAdr();
        }
        $adr = array_unique($adr);
        $algo = $algoRepository->findAll();

        if (!empty($_POST['adr'])) {
            $filterAdr = $request->request->all();
            $filterAdr = $filterAdr['adr'];
            $session = $request->getSession();
            $session->set('adr', $filterAdr);

            $dataFilter = $dataRepository->findByLikeAdr($import->getId(), $filterAdr);
        }

        if (isset($_POST['date']) && !empty($_POST['toDate'])) {
            $session = $request->getSession()->all();
            $filterAdr = $session['adr'];
            $userChoiceDate = explode("/", $_POST['date']);
            $userChoiceToDate = explode("/", $_POST['toDate']);
            for ($i = 0; $i < count($userChoiceDate); $i++) {
                $userChoiceDate[$i] = trim($userChoiceDate[$i], ' ');
                $userChoiceToDate[$i] = trim($userChoiceToDate[$i], ' ');
            }
            $userChoiceDate[0] = explode("-", $userChoiceDate[0]);
            $userChoiceToDate[0] = explode("-", $userChoiceToDate[0]);
            $userChoiceDate[0] = array_reverse($userChoiceDate[0]);
            $userChoiceToDate[0] = array_reverse($userChoiceToDate[0]);
            $userChoiceDate [0] = join("-", $userChoiceDate [0]);
            $userChoiceToDate [0] = join("-", $userChoiceToDate [0]);
            $userChoiceDate = join(' ', $userChoiceDate);
            $userChoiceToDate = join(' ', $userChoiceToDate);
            $dataFilter = $dataRepository->findByDateToLimit($import->getId(), $filterAdr, $userChoiceDate, $userChoiceToDate);
            $session = $request->getSession();
            $session->set('filter', $dataFilter);
        }
        foreach ($dataFilter as $data) {
            $delta1[] = $data->getDelta1();
            $delta2[] = $data->getDelta2();
            $ratioFilter[] = $data->getFilterRatio();
            $slopeTemperatureCorrection[] = $data->getSlopeTemperatureCorrection();
            $rawCo[] = $data->getRawCo();
            $coCorrection[] = $data->getCoCorrection();
            $temperatureCorrection[] = $data->getTemperatureCorrection();
            $datetime[] = date_format($data->getDatetime(), 'd-m-Y  /  H:i:s');
            $alarm[] = $data->getAlarm();
            $status[] = $data->getStatus();
        }
        if (isset($_POST['algo'])) {
            $session = $request->getSession()->all();
            $dataFilter = $session['filter'];
            $filterAdr = $session['adr'];
            $algoChoice = $algoRepository->findBy(['id' => $_POST['algo']]);

            foreach ($dataFilter as $data) {
                $delta1[] = $data->getDelta1();
                $delta2[] = $data->getDelta2();
                $ratioFilter[] = $data->getFilterRatio();
                $slopeTemperatureCorrection[] = $data->getSlopeTemperatureCorrection();
                $rawCo[] = $data->getRawCo();
                $coCorrection[] = $data->getCoCorrection();
                $temperatureCorrection[] = $data->getTemperatureCorrection();
                $datetime[] = date_format($data->getDatetime(), 'd-m-Y  /  H:i:s');
                $alarm[] = $data->getAlarm();
                $status[] = $data->getStatus();
            }
            foreach ($algoChoice as $value) {
                $algoName = $value->getName();
                $ordnance1tmp = $value->getYtmp() - ($value->getCoef2tmp() * $value->getXtmp());
                $ordnance2tmp = $value->getYtmp() - ($value->getCoef2tmp() * $value->getXtmp());
                $ordnance1ratio = $value->getYratio() - ($value->getCoef1ratio() * $value->getXratio());
                $ordnance2ratio = $value->getYratio() - ($value->getCoef2ratio() * $value->getXratio());
                $sdt = $value->getCoef1tmp() * 0 * 2 + $ordnance1tmp;
                for ($i = 0; $i < count($ratioFilter); $i++) {
                    if ($ratioFilter[$i] < $value->getXratio()) {
                        $resultAlgo[$i] = ($value->getCoef1ratio() * $ratioFilter[$i] + $ordnance1ratio) * $sdt;
                    } else {
                        $resultAlgo[$i] = ($value->getCoef2ratio() * $ratioFilter[$i] + $ordnance2ratio) * $sdt;
                    }
                }
            }
        }
        for ($i = 0; $i < count($alarm); $i++) {
            if ($alarm[$i] >= 1) {
                $alarm[$i - 1] = 0;
                $alarm[$i] = max($delta2) + 50;
                $alarm[$i + 1] = 0;
            }
        }
        for ($i = 0; $i < count($status); $i++) {
            if ((key_exists($i + 1, $status)) && ($status[$i] != $status[$i + 1])) {
                $condition[$i] = [$status[$i], $status[$i + 1], $datetime[$i + 1]];
            }
        }
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $datetime,
            'datasets' => [
                [
                    'label' => $algoName,
                    'backgroundColor' => 'rgba( 230, 146, 0 ,0)',
                    'borderColor' => 'rgb(0, 0, 0)',
                    'data' => $resultAlgo,
                    'yAxisID' => 'left-y-axis',
                ],
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
                    'backgroundColor' => 'rgba(255,99,132,0)',
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
                'yAxes' =>
                    [
                        ['ticks' => ['suggestedMin' => 0, 'suggestedMax' => 10,], 'position' => 'left', 'id' => 'left-y-axis',],
                        ['ticks' => ['suggestedMin' => 0, 'suggestedMax' => 12], 'position' => 'right', 'id' => 'right-y-axis'],
                    ],
            ],
            'elements' => [
                'line' => ['tension' => 0],
            ]
        ]);
        return $this->render('graph/graph.html.twig', [
            "chart" => $chart,
            "import" => $import,
            'adrs' => $adr,
            'adrChoice' => $filterAdr,
            'dates' => $datetime,
            'status' => $status,
            'conditions' => $condition,
            'algos' => $algo,
        ]);
    }
}