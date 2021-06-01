<?php


namespace App\Service;


use DateTime;

class Recorder
{
    const EXCLUDE_TREATMENT = [
        '*******************',
        'ID_BLOC_ENCR',
        'BLOC_DATAS',
    ];
    const ALARM_DETECTED = 'STATUS_ALARM';
    const LR_TRIM = ', ';
    const NOT_CALCULATED = 60000;
    const ERROR_DETECT = 64609;
    const RESULT_ERROR = 2048;
    const DIVISION_DATA = 10;
    const RATIO_NOT_CALCULATED = 10;
    private int $counter = 1;
    public array $data = [];
    public int $loop = 0;

    public function treatment(array $recorder): array
    {
        for ($i = 1; $i < count($recorder); $i++) {
            if (
                (!str_contains($recorder[$i], self::EXCLUDE_TREATMENT[0])) &&
                (!str_contains($recorder[$i], self::EXCLUDE_TREATMENT[1])) &&
                (!str_contains($recorder[$i], self::EXCLUDE_TREATMENT[2]))
            ) {
                if ($this->counter === 1) {
                    try {
                        $this->data[$this->loop] = $this->firstTreatment($recorder[$i]);
                        $this->counter++;
                    } catch (\Exception $e) {

                        $this->data['errors'][] = 'Une erreur est survenue à la ligne ' . $i . ' du fichier. ->'. $e->getMessage();
                    }

                } elseif ($this->counter === 2) {
                    try {
                        $this->data[$this->loop - 1]['status'] = $this->secondTreatment($recorder[$i]);
                        $this->counter++;
                    } catch (\Exception $e) {

                        $this->data['errors'][] = 'Une erreur est survenue à la ligne ' . $i . ' du fichier. ->'. $e->getMessage();
                    }
                } elseif ($this->counter === 3) {
                    try {
                        $arrayData = $this->thirdTreatment($recorder[$i]);
                        $this->data[$this->loop - 2]['data'] = $this->calculatedData($arrayData);
                        $this->counter = 1;
                    } catch (\Exception $e) {

                        $this->data['errors'][] = 'Une erreur est survenue à la ligne ' . $i . ' du fichier. ->'. $e->getMessage();
                    }
                }

            }else {
                $this->counter = 1;
            }
            $this->loop++;
            if (!empty($this->data['errors'])) {
                return $this->data;
            }
        }
        return $this->data;
    }

    private function firstTreatment(string $rowData): array
    {
        $data = [];
        $date = substr($rowData, 0, 19);
        $date = str_replace('/', '-', $date);
        $data['adr'] = intval(rtrim(substr(strpbrk($rowData, '='), 1, 3), self::LR_TRIM));
        $data['date'] = new DateTime($date);
        if (str_contains($rowData, self::ALARM_DETECTED)) {
            $data['alarm'] = intval(rtrim(substr($rowData, 61, 2)));
            $data['data'] = $this->recoveryDataAlarm($data['adr']);
            $this->counter = 0;
            $this->loop += 2;
        }
        return $data;
    }

    private function secondTreatment(string $rowData): int
    {
        $status = ltrim(substr($rowData, -3), self::LR_TRIM);
        $status = intval($status);

        return $status;
    }

    private function thirdTreatment(string $rowData): array
    {
        $data = ltrim($rowData, 'FONCTIONAL_STATUS, ');
        $dataClean = explode(self::LR_TRIM,$data);
        $data = $dataClean[13];
        $clean = explode(' ',$data);
        $dataClean[13] = $clean[0];
        if (array_key_exists(14, $dataClean)) {
            unset($dataClean[14]);
        }
        for ($i = 0; $i < count($dataClean); $i += 2) {
            $dataClean[$i] = $dataClean[$i] + (256 * $dataClean[$i + 1]);
        }
        for ($i = 0 ; $i < 14 ; $i += 2) {
            unset($dataClean[$i + 1]);
        }
        return $dataClean;
    }

    private function recoveryDataAlarm(int $adr): array
    {
        $data = [];
        for ($i = $this->loop - 3 ; $i > 1 ; $i -= 3) {
            if ($this->data[$i]['adr'] === $adr) {
                $data = $this->data[$i]['data'];
                $i = 1;
            }
        }
        return $data;
    }

    private function calculatedData(array $data): array
    {
        if ($data[2] > self::ERROR_DETECT) {
            $data[2] = self::RESULT_ERROR;
        }
        if ($data[4] > self::NOT_CALCULATED) {
            $data[4] = self::RATIO_NOT_CALCULATED;
        }
        if ($data[4] != self::RATIO_NOT_CALCULATED) {
            $data[4] = $data[4] / self::DIVISION_DATA;
        }
        for ($i = 0; $i < count($data) * 2; $i += 2) {
            if (($i != 4 && ($data[$i] < self::ERROR_DETECT)) || ($i === 4 && $data[$i] != self::RATIO_NOT_CALCULATED)) {
                $data[$i] = $data[$i] / self::DIVISION_DATA;
            } elseif ($i != 4 && ($data[$i] > self::ERROR_DETECT)) {
                $data[$i] = 0;
            }
        }
        return $data;
    }
}