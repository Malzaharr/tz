<?php
// calculator.php

class DataProcessor
{
    private $data;

    public function __construct(array $data)
    {
        // Сортируем данные по убыванию дня (как в примере)
        krsort($data); 
        $this->data = $data;
    }

    /**
     * Рассчитывает погрешность по формуле для заданного дня.
     * 
     * % = (((V1_curr - V1_prev) - (V2_curr - V2_prev)) / (V1_curr - V1_prev)) * 100%
     */
    private function calculateError(int $day): ?float
    {
        // Если текущий день - самый первый (день 1), погрешность не рассчитывается
        if ($day == 1) {
            return null;
        }

        // Находим текущую запись
        if (!isset($this->data[$day])) {
            return null; // Не должно случиться, если вызываем корректно
        }
        $current = $this->data[$day];

        // Находим предыдущую запись (день - 1)
        $prevDay = $day + 1; // Так как мы идем по убыванию дней, предыдущий день (по времени) это следующий индекс в отсортированном списке.
                             // Для примера, если мы в дне 11, предыдущий день - это день 10, который находится в списке после 11-го.
        
        // Для корректности расчетов нужно найти фактический предыдущий день в истории.
        // Поскольку данные представлены как лог, мы предполагаем, что порядок в файле data.php (после krsort) соответствует хронологии,
        // где индекс $i является "текущим", а индекс $i+1 является "предыдущим" (по времени).
        
        $currentIndex = array_keys($this->data, $current)[0] ?? null;
        
        // Чтобы избежать сложной логики поиска "предыдущего" дня в хаотичном наборе, 
        // лучше переиндексировать массив по порядку, чтобы $data[0] был самым старым, а $data[N] самым новым.

        // Для соответствия формуле, где нужно V_prev, мы должны взять запись, которая была ДО текущей.
        // Если мы обрабатываем массив, отсортированный по убыванию дня (11, 10, 9...), то V_prev это следующая запись в этом массиве.
        
        $allDays = array_keys($this->data);
        
        $currentIdxInKeys = array_search($day, $allDays);
        
        // Если текущий день не последний в списке (т.е. не самый старый), есть предыдущий день (который наступил раньше)
        if ($currentIdxInKeys !== false && $currentIdxInKeys < count($allDays) - 1) {
            $prevDayValue = $allDays[$currentIdxInKeys + 1]; // Фактический предыдущий день по времени
            $previous = $this->data[$prevDayValue];

            // Проверка на отсутствие данных
            if ($current['V1'] === null || $current['V2'] === null || 
                $previous['V1'] === null || $previous['V2'] === null) {
                return null;
            }
            
            $deltaV1 = $current['V1'] - $previous['V1'];
            $deltaV2 = $current['V2'] - $previous['V2'];

            if ($deltaV1 == 0) {
                return 0.00; // Или обработать деление на ноль, если это возможно по условию
            }

            // Формула: (((V1_curr - V1_prev) - (V2_curr - V2_prev)) / (V1_curr - V1_prev)) * 100
            $percentage = (($deltaV1 - $deltaV2) / $deltaV1) * 100;
            
            return $percentage;

        }
        
        return null; // День 1 или предыдущие данные отсутствуют
    }

    /**
     * Обрабатывает все данные и готовит их к рендерингу.
     */
    public function processData(): array
    {
        $processed = [];
        
        // Сортируем ключи для правильного обхода (чтобы день 1 был последним в цикле)
        $days = array_keys($this->data);
        sort($days); // Сортируем 1, 2, 3... 11

        foreach ($days as $day) {
            $record = $this->data[$day];
            $row = [
                'Day' => $day,
                'V1' => null,
                'V2' => null,
                '%' => null,
                'highlight' => false
            ];

            // Обработка отсутствующих данных (если в data.php установлено null)
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
                // Форматирование для вывода и проверка условия подсветки
                $row['%_formatted'] = number_format($error, 2) . '%';
                
                if ($error > 7.4) {
                    $row['highlight'] = true;
                }
            } else {
                 $row['%_formatted'] = ''; // Пусто для дня 1
            }

            $processed[$day] = $row;
        }
        
        // Финальная сортировка по убыванию дня для вывода на странице (11, 10, 9...)
        krsort($processed);
        return array_values($processed);
    }
}

// --- Блок для использования в index.php ---
/*
$rawData = require 'data.php';
$processor = new DataProcessor($rawData);
$tableData = $processor->processData();
*/