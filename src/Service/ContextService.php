<?php


namespace App\Service;


use DateTime;

class ContextService
{
    const TIMESTAMP_1970_2010 = 1262304000;

    public function leadingTreatment(array $data): array
    {
        $lead = [];
        $row = 1;
        try {
            for ($i = 0; $i < 6; $i++) {
                $lead[$i] = $data[$i];
                $row++;
            }
        } catch (\Exception $e) {
            $lead['errors'] = 'Une erreur est survenue à la ligne' . $row;
        }
        return $lead;
    }

    public function contextTreatment(array $data, int $primary): array
    {
        $dataClean = [];
        try {
            $dataClean['algo'] = $data[$primary];
            $dataClean['caseEvaluation'] = $data[$primary + 1];
            $dataClean['halfContext'] = $data[$primary + 2];
            $dataClean['productIdentifier'] = $data[$primary + 3] + (256 * $data[$primary + 4]);
            date_default_timezone_set('Europe/London');
                        $timeStamp = (
                            ($data[$primary + 8] * 16777216) +
                            ($data[$primary + 7] * 65536) +
                            ($data[$primary + 6] * 256) +
                            $data[$primary + 5]
                        );
            $date = getdate(self::TIMESTAMP_1970_2010 + $timeStamp);
            $date = $date['year'] . '-' . $date['mon'] . '-' . $date['mday'] . ' ' . $date['hours'] . ':' . $date['minutes'] . ':' . $date['seconds'];
            $dataClean['date'] = new datetime($date);
            $dataClean['encr1'] = $data[$primary + 9];
            $dataClean['encr2'] = $data[$primary + 10];
            if ($data[$primary] >= 10) {
                $dataClean['ratio'] = $data[$primary + 11] /10;
                $dataClean['delta2'] = ($data[$primary + 12] + (256 * $data[$primary + 13])) /10;
                $dataClean['tempAlarm'] = 0;
                $dataClean['velocimeter'] =  0;
            } else {
                $dataClean['ratio'] = 0;
                $dataClean['delta2'] = 0;
                $dataClean['tempAlarm'] = ($data[$primary + 11] + (256 *  $data[$primary + 12])) /10;
                $dataClean['velocimeter'] =  ($data[$primary + 13] + ( 256 *  $data[$primary + 14])) /10;
            }
            $dataClean['slopeTemp'] =  $data[$primary + 15];
        } catch (\Exception $e) {
            $dataClean['errors'] = 'Une erreur est survenue entre la ligne ' . $primary . ' et ' . ($primary + 15);
        }
        return $dataClean;
    }

    public function elementaryTreatment(array $data, int $primary): array
    {
        $dataClean = [];
        try {
        $dataClean['ratio'] = intval($data[$primary] /10);
        $dataClean['delta1'] = ($data[$primary + 1]/10 )+ ( 256 * ($data[$primary + 2] / 10));
        $dataClean['pulse1'] = $data[$primary + 3] + ( 256 * $data[$primary + 4]);
        $dataClean['delta2'] = ($data[$primary + 5] / 10) + ( 256 * ($data[$primary + 6] /10));
        $dataClean['pulse2'] = $data[$primary + 7] + ( 256 * $data[$primary + 8]);
        $dataClean['rawTemp'] = $data[$primary + 9] + ( 256 * $data[$primary + 10]);
        $dataClean['slopeTemp'] = ($data[$primary + 11] + ( 256 * $data[$primary + 12]))/10;
        $dataClean['co'] = $data[$primary + 13] + ( 256 * $data[$primary + 14]);

        } catch (\Exception $e) {
            $dataClean['errors'] = 'Une erreur est survenue entre la ligne ' . $primary . ' et ' . ($primary + 14);
        }
        return $dataClean;
    }
}