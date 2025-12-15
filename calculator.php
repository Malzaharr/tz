<?php
class DataProcessor
{
    private $data;

    public function __construct(array $data)
    {
        krsort($data); 
        $this->data = $data;
    }

    private function calculateError(int $day): ?float
    {
        if ($day == 1) {
            return null;
        }
        if (!isset($this->data[$day])) {
            return null;
        }
        $current = $this->data[$day];
        $allDays = array_keys($this->data);
        
        $currentIdxInKeys = array_search($day, $allDays);
        if ($currentIdxInKeys !== false && $currentIdxInKeys < count($allDays) - 1) {
            $prevDayValue = $allDays[$currentIdxInKeys + 1];
            $previous = $this->data[$prevDayValue];

            if ($current['V1'] === null || $current['V2'] === null || 
                $previous['V1'] === null || $previous['V2'] === null) {
                return null;
            }
            
            $deltaV1 = $current['V1'] - $previous['V1'];
            $deltaV2 = $current['V2'] - $previous['V2'];

            if ($deltaV1 == 0) {
                return 0.00;
            }

            $percentage = (($deltaV1 - $deltaV2) / $deltaV1) * 100;
            
            return $percentage;

        }
        
        return null;
    }

    public function processData(): array
    {
        $processed = [];

        $days = array_keys($this->data);
        sort($days);

        foreach ($days as $day) {
            $record = $this->data[$day];
            $row = [
                'Day' => $day,
                'V1' => null,
                'V2' => null,
                '%' => null,
                'highlight' => false
            ];

            if ($record === null || !is_array($record)) {
                $row['V1'] = 'Данные отсутствуют';
                $row['V2'] = 'Данные отсутствуют';
                $processed[$day] = $row;
                continue;
            }

            $row['V1'] = $record['V1'];
            $row['V2'] = $record['V2'];

            $error = $this->calculateError($day);
            
            if ($error !== null) {
                $row['%'] = $error;
                $row['%_formatted'] = number_format($error, 2) . '%';
                
                if ($error > 7.4) {
                    $row['highlight'] = true;
                }
            } else {
                 $row['%_formatted'] = '';
            }

            $processed[$day] = $row;
        }
        
        krsort($processed);
        return array_values($processed);
    }
}