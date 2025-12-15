<?php
// index.php

// 1. Инициализация логики (В будущем этот вызов может быть перенесен в CLI)
require 'calculator.php';

$rawData = require 'data.php';
$processor = new DataProcessor($rawData);
$tableData = $processor->processData();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отчет по объему воды</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Отчет по показаниям датчиков</h1>

    <table>
        <thead>
            <tr>
                <th>День</th>
                <th>V1 (м³)</th>
                <th>V2 (м³)</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tableData as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Day']) ?></td>
                    <td>
                        <?php 
                        // Проверяем, является ли значение строкой (т.е. "Данные отсутствуют")
                        if (is_string($row['V1'])) {
                            echo htmlspecialchars($row['V1']);
                        } else {
                            echo number_format($row['V1'], 2);
                        }
                        ?>
                    </td>
                    <td>
                         <?php 
                        if (is_string($row['V2'])) {
                            echo htmlspecialchars($row['V2']);
                        } else {
                            echo number_format($row['V2'], 2);
                        }
                        ?>
                    </td>
                    <td class="<?= $row['highlight'] ? 'highlight' : '' ?>">
                        <?php 
                        // Выводим форматированную строку с процентом или пустую строку для дня 1
                        echo htmlspecialchars($row['%_formatted']); 
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>