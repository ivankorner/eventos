<?php
/**
 * Controlador de exportaciones (Excel, CSV, PDF)
 */

class ExportController
{
    private SubmissionModel $subModel;
    private EventModel      $eventModel;
    private FormModel       $formModel;

    public function __construct()
    {
        $this->subModel   = new SubmissionModel();
        $this->eventModel = new EventModel();
        $this->formModel  = new FormModel();
    }

    /**
     * GET /admin/eventos/{id}/exportar/excel
     */
    public function excel(array $params = []): void
    {
        $event      = $this->findEventOrFail((int)$params['id']);
        $form       = $this->formModel->findByEvent($event['id']);
        $formFields = $form ? $this->formModel->getFields($form['id']) : [];
        $subs       = $this->subModel->getAllByEvent($event['id']);

        AuditLogModel::log('export.excel', 'Event', $event['id']);

        Excel::downloadSubmissions($subs, $formFields, $event['title']);
    }

    /**
     * GET /admin/eventos/{id}/exportar/csv
     */
    public function csv(array $params = []): void
    {
        $event      = $this->findEventOrFail((int)$params['id']);
        $form       = $this->formModel->findByEvent($event['id']);
        $formFields = $form ? $this->formModel->getFields($form['id']) : [];
        $subs       = $this->subModel->getAllByEvent($event['id']);

        AuditLogModel::log('export.csv', 'Event', $event['id']);

        Excel::downloadCsv($subs, $formFields, $event['title']);
    }

    /**
     * GET /admin/eventos/{id}/exportar/pdf
     */
    public function pdfEvent(array $params = []): void
    {
        $event      = $this->findEventOrFail((int)$params['id']);
        $form       = $this->formModel->findByEvent($event['id']);
        $formFields = $form ? $this->formModel->getFields($form['id']) : [];
        $subs       = $this->subModel->getAllByEvent($event['id']);

        AuditLogModel::log('export.pdf_event', 'Event', $event['id']);

        $html = Pdf::eventSummary($event, $subs, $formFields);
        Pdf::download($html, 'inscripciones-' . $event['slug'] . '.pdf');
    }

    /**
     * GET /admin/inscripciones/{id}/exportar/pdf
     */
    public function pdfSubmission(array $params = []): void
    {
        $sub = $this->subModel->findWithData((int)$params['id']);
        if (!$sub) {
            http_response_code(404);
            die('Inscripción no encontrada.');
        }

        $event      = $this->eventModel->find($sub['event_id']);
        $form       = $this->formModel->findByEvent($sub['event_id']);
        $formFields = $form ? $this->formModel->getFields($form['id']) : [];

        AuditLogModel::log('export.pdf_submission', 'Submission', $sub['id']);

        $html = Pdf::submission($sub, $event, $formFields);
        Pdf::download($html, 'inscripcion-' . $sub['id'] . '.pdf');
    }

    private function findEventOrFail(int $id): array
    {
        $event = $this->eventModel->find($id);
        if (!$event || $event['deleted_at'] !== null) {
            http_response_code(404);
            die('Evento no encontrado.');
        }
        $user = Session::user();
        if ($user['role'] !== 'super_admin' && $event['user_id'] !== $user['id']) {
            http_response_code(403);
            die('Sin permisos para exportar este evento.');
        }
        return $event;
    }
}
