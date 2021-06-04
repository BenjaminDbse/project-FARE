<?php


namespace App\Service;


use DateTime;

class Context
{
    const NUMBER_OF_CONTEXT = 4;
    const NUMBER_OF_ELEMENTARY = 15;
    private int $counter = 1;
    public int $row = 1;
    private int $loop = 10;
    public array $data = [];


    public function treatment(array $data): array
    {
        for ($i = 0; $i < count($data); $i++) {
            if ($this->counter === 1) {
                try {
                    $this->data['header'] = $this->firstTreatment($data);
                    $this->counter++;
                } catch (\Exception $e) {
                    $this->data['errors'][] = 'Une erreur est survenue à la ligne ' . $this->row . 'du fichier.';
                }
            } elseif ($this->counter === 2) {
                try {
                    $this->data['context'] = $this->contextTreatment($data);
                    $this->counter++;
                } catch (\Exception $e) {
                    $this->data['errors'][] = 'Une erreur est survenue à la ligne ' . $this->row . 'du fichier.';
                }
            }
        }
            return $this->data;
        }

        private
        function firstTreatment(array $data): array
        {
            $arrayData = [];
            for ($i = 0; $i < 6; $i++) {
                $arrayData[] = intval($data[$i]);
                $this->row++;
            }
            return $arrayData;
        }

        private
        function contextTreatment(array $data): array
        {
            $arrayData = [];
            for ($i = 0; $i < self::NUMBER_OF_CONTEXT; $i++) {
                $arrayData[$i] = $data[$this->loop + $i];
            }
            return $arrayData;
        }
    }