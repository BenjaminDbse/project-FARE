<?php

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
for ($i = 0 ; $i < count($status) ; $i++) {
    if ((key_exists($i + 1, $status)) && ($status[$i] != $status[$i + 1])) {
        $condition[$i] = [$status[$i], $status[$i + 1], $datetime[$i + 1]];
    }
}
$max = [
    max($delta1),
    max($delta2),
    max($slopeTemperatureCorrection),
    max($rawCo),
    max($temperatureCorrection),
];