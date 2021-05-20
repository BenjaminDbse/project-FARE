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
    const VALUE_ALARM = 50;
    private array $delta1 = [];
    private array $delta2 = [];
    private array $ratioFilter = [];
    private array $temperatureCorrection = [];
    private array $slopeTemperatureCorrection = [];
    private array $rawCo = [];
    private array $coCorrection = [];
    private array $status = [];
    private array $datetime = [];
    private array $alarm = [];
    private string $filterAdr = 'Aucune adresse sélectionnée';
    private array $condition = [];
    private array $resultAlgo = [];
    private string $algoName = 'Algo non défini';
    private array $dateChoice = [];
    private Import $import;
    private array $dataFilter;
    private array $curveEstimateAlarm = [];

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
        $adr = [];
        foreach ($import->getData() as $data) {
            $adr[] = $data->getAdr();
        }
        $adr = array_unique($adr);
        $algo = $algoRepository->findAll();
        $this->import = $import;
        if (isset($_POST['adr'])) {
            $this->postAdr($request, $dataRepository);
            $this->treatmentDataFilter($this->dataFilter);
        }
        if (isset($_POST['date']) && !empty($_POST['toDate'])) {
            $startDate = $_POST['date'];
            $endDate = $_POST['toDate'];

            $this->postDate($request, $dataRepository, $startDate, $endDate);
            $this->treatmentDataFilter($this->dataFilter);
        }

        if (isset($_POST['algo'])) {
            $nbAlgo = $_POST['algo'];
            $session = $request->getSession()->all();
            $this->dataFilter = $session['filter'];
            $this->filterAdr = $session['adr'];
            $algoChoice = $this->treatmentDataAlgo($algoRepository, $nbAlgo);
            $this->treatmentDataFilter($this->dataFilter);
            $algoCalculated = $this->calculatedAlgo($algoChoice);
            $this->resultAlgo = $this->curveAlgo($algoCalculated, $nbAlgo);
            $this->estimateAlarm();
        }
        if (isset($_POST['algo']) || isset($_POST['adr']) || isset($_POST['date'])) {
            $this->treatmentAlarm();
            $this->treatmentStatus();
            $session = $request->getSession()->all();
            $this->dateChoice = $session['date'];
        }
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $this->datetime,
            'datasets' => [
                [
                    'label' => $this->algoName,
                    'backgroundColor' => 'rgba( 230, 146, 0 ,0)',
                    'borderColor' => 'rgb(0, 0, 0)',
                    'data' => $this->resultAlgo,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Estimation Alarme',
                    'backgroundColor' => 'rgba( 193, 87, 0 ,0.5)',
                    'borderColor' => 'rgb(193, 87, 0)',
                    'data' => $this->curveEstimateAlarm,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Delta 1',
                    'backgroundColor' => 'rgba( 233, 117, 0 ,0)',
                    'borderColor' => 'rgb(233, 117, 0)',
                    'data' => $this->delta1,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Delta 2',
                    'backgroundColor' => 'rgba(2, 2, 200,0)',
                    'borderColor' => 'rgb(2, 2, 200)',
                    'data' => $this->delta2,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Alarme',
                    'backgroundColor' => 'rgba(255, 0, 0,0.1)',
                    'borderColor' => 'rgb(255, 0, 0)',
                    'data' => $this->alarm,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Température corrigée',
                    'backgroundColor' => 'rgba(255, 99, 132,0)',
                    'borderColor' => 'rgb( 203, 11, 198)',
                    'data' => $this->temperatureCorrection,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Pente de température',
                    'backgroundColor' => 'rgba(19,182,8,0)',
                    'borderColor' => 'rgb(19,182,8)',
                    'data' => $this->slopeTemperatureCorrection,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'CO Brut',
                    'backgroundColor' => 'rgba(255,99,132,0)',
                    'borderColor' => 'rgb( 128, 128, 128)',
                    'data' => $this->rawCo,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'CO Corrigé',
                    'backgroundColor' => 'rgba(91, 0, 0,0)',
                    'borderColor' => 'rgb(91, 0, 0)',
                    'data' => $this->coCorrection,
                    'yAxisID' => 'left-y-axis',
                ],
                [
                    'label' => 'Ratio filtré',
                    'backgroundColor' => 'rgba(255, 255, 255,0)',
                    'borderColor' => 'rgb(83, 108, 0)',
                    'data' => $this->ratioFilter,
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
            ],
            "maintainAspectRatio" => false,
        ]);
        return $this->render('graph/graph.html.twig', [
            "chart" => $chart,
            "import" => $import,
            'adrs' => $adr,
            'adrChoice' => $this->filterAdr,
            'dates' => $this->dateChoice,
            'status' => $this->status,
            'conditions' => $this->condition,
            'algos' => $algo,
        ]);
    }

    private function postAdr(Request $request, DataRepository $dataRepository)
    {
        $filterAdr = $request->request->all();
        $this->filterAdr = $filterAdr['adr'];
        $session = $request->getSession();
        $session->set('adr', $this->filterAdr);

        $this->dataFilter = $dataRepository->findByLikeAdr($this->import->getId(), $this->filterAdr);
        $session = $request->getSession();
        $session->set('filter', $this->dataFilter);
        foreach ($this->dataFilter as $data) {
            $this->dateChoice [] = date_format($data->getDatetime(), 'd-m-Y  /  H:i:s');
        }
        $session = $request->getSession();
        $session->set('date', $this->dateChoice);
    }

    private function postDate(Request $request, DataRepository $dataRepository, string $startDate, string $endDate)
    {
        $filterAdr = $request->getSession()->all();
        $this->filterAdr = $filterAdr['adr'];

        $startDate = $this->treatmentDate($startDate);
        $endDate = $this->treatmentDate($endDate);

        $this->dataFilter = $dataRepository->findByDateToLimit($this->import->getId(), $this->filterAdr, $startDate, $endDate);

        $session = $request->getSession();
        $session->set('filter', $this->dataFilter);

        foreach ($this->dataFilter as $data) {
            $this->dateChoice [] = date_format($data->getDatetime(), 'd-m-Y  /  H:i:s');
        }
    }

    private function treatmentDate(string $date): string
    {
        $date = explode("/", $date);
        for ($i = 0; $i < count($date); $i++) {
            $date[$i] = trim($date[$i], ' ');
        }
        $date[0] = explode("-", $date[0]);
        $date[0] = array_reverse($date[0]);
        $date [0] = join("-", $date [0]);

        return join(' ', $date);
    }

    private function treatmentDataFilter(array $dataFilter)
    {
        foreach ($dataFilter as $data) {
            $this->delta1[] = $data->getDelta1();
            $this->delta2[] = $data->getDelta2();
            $this->ratioFilter[] = $data->getFilterRatio();
            $this->slopeTemperatureCorrection[] = $data->getSlopeTemperatureCorrection();
            $this->rawCo[] = $data->getRawCo();
            $this->coCorrection[] = $data->getCoCorrection();
            $this->datetime[] = date_format($data->getDatetime(), 'd-m-Y  /  H:i:s');
            $this->temperatureCorrection[] = $data->getTemperatureCorrection();
            $this->alarm[] = $data->getAlarm();
            $this->status[] = $data->getStatus();
        }
    }

    private function treatmentDataAlgo(AlgoRepository $algoRepository, $nbAlgo): array
    {

        return $algoRepository->findBy(['id' => $nbAlgo]);
    }

    private function calculatedAlgo(array $algo): array
    {
        $algoCalculated = [];
        foreach ($algo as $value) {
            $this->algoName = (string)$value->getName();
            $algoCalculated['ordnance1tmp'] = $value->getYtmp() - ($value->getCoef1tmp() * $value->getXtmp());
            $algoCalculated['$ordnance2tmp'] = $value->getYtmp() - ($value->getCoef2tmp() * $value->getXtmp());
            $algoCalculated['$ordnance1ratio'] = $value->getYratio() - ($value->getCoef1ratio() * $value->getXratio());
            $algoCalculated['$ordnance2ratio'] = $value->getYratio() - ($value->getCoef2ratio() * $value->getXratio());
            $algoCalculated['ytmp'] = $value->getYtmp();
            $algoCalculated['xtmp'] = $value->getXtmp();
            $algoCalculated['yratio'] = $value->getYratio();
            $algoCalculated['xratio'] = $value->getXratio();
            $algoCalculated['coef1ratio'] = $value->getCoef1ratio();
            $algoCalculated['coef2ratio'] = $value->getCoef2ratio();
            $algoCalculated['coef1tmp'] = $value->getCoef1tmp();
            $algoCalculated['coef2tmp'] = $value->getCoef2tmp();


        }
        return $algoCalculated;
    }

    private function curveAlgo(array $algo, $nbAlgo): array
    {
        $sdt = [];
        $resultAlgo = [];


        for ($i = 0; $i < count($this->temperatureCorrection); $i++) {
            if ($this->slopeTemperatureCorrection[$i] * 2 < $algo['xtmp']) {
                $sdt [] = $algo['coef1tmp'] * $this->slopeTemperatureCorrection[$i] * 2 + $algo['ordnance1tmp'];
            } else {
                $sdt [] = $algo['coef2tmp'] * $this->slopeTemperatureCorrection[$i] * 2 + $algo['coef2tmp'];
            }
        }
        for ($i = 0; $i < count($this->ratioFilter); $i++) {
            if ($this->ratioFilter[$i] < $algo['xratio']) {
                $resultAlgo[$i] = ($algo['coef1ratio'] * $this->ratioFilter[$i] + $algo['$ordnance1ratio']) * $sdt[$i];
            } else {
                $resultAlgo[$i] = ($algo['coef2ratio'] * $this->ratioFilter[$i] + $algo['$ordnance2ratio']) * $sdt[$i];
            }
        }
        if ($nbAlgo > 11) {
            for ($i = 0; $i < count($this->coCorrection); $i++) {
                if ($this->coCorrection[$i] > 10) {
                    $resultAlgo[$i] = $resultAlgo[$i] * 1.5;
                }
            }
        }
        return $resultAlgo;
    }

    private function treatmentAlarm()
    {
        for ($i = 0; $i < count($this->alarm); $i++) {
            if ($this->alarm[$i] >= 1) {
                $this->alarm[$i - 1] = 0;
                $this->alarm[$i] = max($this->delta2) + self::VALUE_ALARM;
                $this->alarm[$i + 1] = 0;
            }
        }
    }

    private function treatmentStatus()
    {
        for ($i = 0; $i < count($this->status); $i++) {
            if ((key_exists($i + 1, $this->status)) && ($this->status[$i] != $this->status[$i + 1])) {
                $this->datetime[$i + 1] = str_replace('/', 'à', $this->datetime[$i + 1]);
                $this->condition[$i] = [$this->status[$i], $this->status[$i + 1], $this->datetime[$i + 1]];
            }
        }
    }
    private function estimateAlarm()
    {
        for ($i = 0 ; $i < count($this->delta2) ; $i++) {
            if ($this->delta2[$i] > $this->resultAlgo[$i]) {
                $this->curveEstimateAlarm[$i] = max($this->delta2) +  self::VALUE_ALARM ;
            } else {
                $this->curveEstimateAlarm[$i] = null;
            }
        }
    }
}