<?php
/**
 * Wrapper de PhpSpreadsheet para exportar inscripciones a Excel y CSV
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class Excel
{
    /**
     * Descarga las inscripciones de un evento como Excel (.xlsx)
     */
    public static function downloadSubmissions(array $submissions, array $formFields, string $eventTitle): void
    {
        $spreadsheet = self::buildSpreadsheet($submissions, $formFields, $eventTitle);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . self::safeFilename($eventTitle) . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Descarga las inscripciones como CSV
     */
    public static function downloadCsv(array $submissions, array $formFields, string $eventTitle): void
    {
        $spreadsheet = self::buildSpreadsheet($submissions, $formFields, $eventTitle);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . self::safeFilename($eventTitle) . '.csv"');
        header('Cache-Control: max-age=0');

        // BOM para que Excel abra correctamente el CSV en UTF-8
        echo "\xEF\xBB\xBF";

        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->save('php://output');
        exit;
    }

    /**
     * Construye el Spreadsheet a partir de las inscripciones y la definición del formulario
     */
    private static function buildSpreadsheet(array $submissions, array $formFields, string $title): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($title, 0, 31));

        // Filtrar campos presentacionales (heading, paragraph)
        $dataFields = array_filter($formFields, fn($f) => !in_array($f['type'], ['heading', 'paragraph']));
        $dataFields = array_values($dataFields);

        // --- Cabeceras ---
        $col = 1;
        $headers = ['#', 'Fecha de envío', 'Estado', 'IP'];
        foreach ($dataFields as $field) {
            $headers[] = $field['label'] ?? 'Campo';
        }

        foreach ($headers as $i => $header) {
            $cell = $sheet->getCellByColumnAndRow($i + 1, 1);
            $cell->setValue($header);
        }

        // Estilo de la fila de cabeceras
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Auto-width para columnas (aproximado)
        foreach (range(1, count($headers)) as $colIdx) {
            $sheet->getColumnDimensionByColumn($colIdx)->setAutoSize(true);
        }

        // --- Datos ---
        $row = 2;
        foreach ($submissions as $i => $sub) {
            $responseData = is_string($sub['response_data'])
                ? json_decode($sub['response_data'], true)
                : $sub['response_data'];

            $statusLabels = ['pending' => 'Pendiente', 'confirmed' => 'Confirmada', 'cancelled' => 'Cancelada'];

            $sheet->getCellByColumnAndRow(1, $row)->setValue($i + 1);
            $sheet->getCellByColumnAndRow(2, $row)->setValue(
                date('d/m/Y H:i', strtotime($sub['submitted_at']))
            );
            $sheet->getCellByColumnAndRow(3, $row)->setValue(
                $statusLabels[$sub['status']] ?? $sub['status']
            );
            $sheet->getCellByColumnAndRow(4, $row)->setValue($sub['ip_address'] ?? '');

            $colIdx = 5;
            foreach ($dataFields as $field) {
                $value = $responseData[$field['id']] ?? '';
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $sheet->getCellByColumnAndRow($colIdx, $row)->setValue((string)$value);
                $colIdx++;
            }

            // Alternar color de fila para legibilidad
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                ]);
            }

            $row++;
        }

        return $spreadsheet;
    }

    /**
     * Genera un nombre de archivo seguro para la descarga
     */
    private static function safeFilename(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9\-_]/', '-', Slug::generate($name));
        return 'inscripciones-' . $name . '-' . date('Ymd');
    }
}
