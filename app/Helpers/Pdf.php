<?php
/**
 * Wrapper de DomPDF para generación de PDFs
 */

use Dompdf\Dompdf;
use Dompdf\Options;

class Pdf
{
    /**
     * Genera un PDF a partir de HTML y lo descarga
     *
     * @param string $html     HTML completo del documento
     * @param string $filename Nombre del archivo para la descarga
     */
    public static function download(string $html, string $filename = 'documento.pdf'): void
    {
        $pdf = self::create($html);
        $pdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    /**
     * Genera un PDF y devuelve el contenido como string
     */
    public static function toString(string $html): string
    {
        $pdf = self::create($html);
        return $pdf->output();
    }

    /**
     * Crea y renderiza una instancia de Dompdf
     */
    private static function create(string $html): Dompdf
    {
        $fontCache = STORAGE_PATH . '/fonts/cache';
        $tempDir = STORAGE_PATH . '/tmp';

        // Crear directorios si no existen
        @mkdir($fontCache, 0755, true);
        @mkdir($tempDir, 0755, true);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', PUBLIC_PATH);
        $options->set('fontCache', $fontCache);
        $options->set('tempDir', $tempDir);
        $options->set('logOutputFile', $tempDir . '/dompdf.log');

        $dompdf = new Dompdf($options);

        // Usar isPhpEnabled para cargar recursos locales
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }

    /**
     * Renderiza la vista de PDF de una inscripción
     */
    public static function submission(array $submission, array $event, array $formFields): string
    {
        // Capturar la vista como string
        ob_start();
        $data = compact('submission', 'event', 'formFields');
        extract($data);
        include VIEWS_PATH . '/exports/submission-pdf.php';
        return ob_get_clean();
    }

    /**
     * Renderiza la vista de PDF resumen de un evento con todas sus inscripciones
     */
    public static function eventSummary(array $event, array $submissions, array $formFields): string
    {
        ob_start();
        include VIEWS_PATH . '/exports/event-pdf.php';
        return ob_get_clean();
    }
}
