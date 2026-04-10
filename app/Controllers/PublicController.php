<?php
/**
 * Controlador de la parte pública del sistema
 * Landing de eventos, detalle y formulario de inscripción
 */

class PublicController
{
    private EventModel      $eventModel;
    private FormModel       $formModel;
    private SubmissionModel $subModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
        $this->formModel  = new FormModel();
        $this->subModel   = new SubmissionModel();
    }

    /**
     * GET /
     */
    public function index(array $params = []): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 9;

        $total  = $this->eventModel->countPublished();
        $pag    = new Paginator($total, $perPage, $page);
        $events = $this->eventModel->getPublished($pag->limit(), $pag->offset());

        // Configuración del sistema (hero, etc.)
        $settings = $this->getSettings();

        $this->render('public/index', [
            'events'      => $events,
            'paginator'   => $pag,
            'settings'    => $settings,
            'footerText'  => $settings['footer_text'] ?? '',
            'pageTitle'   => $settings['hero_title'] ?? APP_NAME,
        ]);
    }

    /**
     * GET /evento/{slug}
     */
    public function show(array $params = []): void
    {
        $event = $this->eventModel->findBySlug($params['slug'] ?? '');

        if (!$event || $event['status'] !== 'published') {
            http_response_code(404);
            include VIEWS_PATH . '/errors/404.php';
            return;
        }

        $form       = $this->formModel->findByEvent($event['id']);
        $formFields = $form ? $this->formModel->getFields($form['id']) : [];
        $settings   = $form ? $this->formModel->getSettings($form['id']) : [];

        // Verificar cupo disponible
        $isFull   = false;
        $totalSubs = $this->subModel->countByEvent($event['id']);
        if ($event['max_capacity'] && $totalSubs >= $event['max_capacity']) {
            $isFull = true;
        }

        // Estado del evento
        $now       = time();
        $isExpired = $event['end_date'] && strtotime($event['end_date']) < $now;
        $canSubmit = $form && $form['is_active'] && !$isFull && !$isExpired && $event['status'] === 'published';

        // Configuración global del sistema
        $globalSettings = $this->getSettings();

        $this->render('public/event', [
            'event'       => $event,
            'form'        => $form,
            'formFields'  => $formFields,
            'formSettings'=> $settings,
            'footerText'  => $globalSettings['footer_text'] ?? '',
            'isFull'      => $isFull,
            'isExpired'   => $isExpired,
            'canSubmit'   => $canSubmit,
            'totalSubs'   => $totalSubs,
            'csrf'        => Csrf::field(),
            'errors'      => Session::getFlash('errors') ?? [],
            'old'         => Session::getFlash('old') ?? [],
            'pageTitle'   => $event['title'],
            'metaDesc'    => $event['meta_description'],
            'ogImage'     => $event['cover_image'] ? APP_URL . '/' . $event['cover_image'] : '',
        ], 'public');
    }

    /**
     * POST /evento/{slug}/inscribirse
     */
    public function submit(array $params = []): void
    {
        Csrf::verify();

        $slug  = $params['slug'] ?? '';
        $event = $this->eventModel->findBySlug($slug);

        if (!$event || $event['status'] !== 'published') {
            http_response_code(404);
            die('Evento no encontrado.');
        }

        $form = $this->formModel->findByEvent($event['id']);
        if (!$form || !$form['is_active']) {
            Session::flash('error', 'El formulario de inscripción no está disponible.');
            header('Location: ' . APP_URL . '/evento/' . $slug);
            exit;
        }

        $formFields   = $this->formModel->getFields($form['id']);
        $formSettings = $this->formModel->getSettings($form['id']);

        // Validar los campos del formulario
        $responseData = [];
        $errors       = [];
        $emailField   = null;

        foreach ($formFields as $field) {
            if (in_array($field['type'], ['heading', 'paragraph'], true)) {
                continue;
            }

            $fieldId = $field['id'];
            $value   = $_POST[$fieldId] ?? null;

            // Manejar checkboxes (array)
            if ($field['type'] === 'checkbox' && is_array($value)) {
                $responseData[$fieldId] = $value;
            } elseif ($field['type'] === 'file' && isset($_FILES[$fieldId])) {
                // Procesar archivo adjunto
                if (!empty($_FILES[$fieldId]['name'])) {
                    try {
                        $uploaded              = Upload::process($_FILES[$fieldId], 'submissions');
                        $responseData[$fieldId] = $uploaded['path'];
                    } catch (\RuntimeException $e) {
                        $errors[$fieldId][] = $e->getMessage();
                        continue;
                    }
                } else {
                    $responseData[$fieldId] = '';
                }
            } else {
                $responseData[$fieldId] = is_string($value) ? trim($value) : '';
            }

            // Validaciones
            $val = $responseData[$fieldId];
            $label = $field['label'] ?? 'Campo';

            if (!empty($field['required']) && (empty($val) || (is_array($val) && count($val) === 0))) {
                $errors[$fieldId][] = "El campo «{$label}» es obligatorio.";
            }

            if ($field['type'] === 'email' && !empty($val) && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                $errors[$fieldId][] = "El campo «{$label}» debe ser un email válido.";
                $emailField = $fieldId;
            } elseif ($field['type'] === 'email' && !empty($val)) {
                $emailField = $fieldId;
            }

            // Validaciones de longitud
            if (!empty($field['validations']['min_length']) && !empty($val) && mb_strlen($val) < $field['validations']['min_length']) {
                $errors[$fieldId][] = "El campo «{$label}» debe tener al menos {$field['validations']['min_length']} caracteres.";
            }
            if (!empty($field['validations']['max_length']) && !empty($val) && mb_strlen($val) > $field['validations']['max_length']) {
                $errors[$fieldId][] = "El campo «{$label}» no puede superar los {$field['validations']['max_length']} caracteres.";
            }
        }

        // Verificar duplicados si no se permiten
        if (empty($formSettings['allow_duplicates']) && $emailField && !empty($responseData[$emailField])) {
            if ($this->subModel->emailAlreadyRegistered($event['id'], $emailField, $responseData[$emailField])) {
                $errors[$emailField][] = 'Este email ya tiene una inscripción registrada para este evento.';
            }
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old', array_filter($responseData, fn($v) => is_string($v)));
            header('Location: ' . APP_URL . '/evento/' . $slug);
            exit;
        }

        // Guardar inscripción
        $ip = RateLimit::getClientIp();
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $subId = $this->subModel->create($form['id'], $event['id'], $responseData, $ip, $ua);

        // Enviar email de confirmación al inscripto (directo, NO bloquea si falla)
        if ($emailField && !empty($responseData[$emailField])) {
            try {
                $toEmail  = $responseData[$emailField];
                $toName   = $responseData[array_keys($responseData)[0]] ?? 'Inscripto/a';
                $html     = Email::buildConfirmationHtml($event, $formFields, $responseData, $subId);
                Email::send($toEmail, $toName, 'Confirmación de inscripción — ' . $event['title'], $html);
                ErrorHandler::log("Confirmación enviada a {$toEmail}");
            } catch (\Throwable $e) {
                ErrorHandler::log('Error enviando confirmación a ' . ($toEmail ?? 'desconocido') . ': ' . $e->getMessage());
            }
        }

        // Enviar notificación al organizador (directo, NO bloquea si falla)
        $notifyEmail = $formSettings['notify_email'] ?? $event['notify_email'] ?? '';
        if ($notifyEmail) {
            try {
                $html = Email::buildNotificationHtml($event, $formFields, $responseData, date('d/m/Y H:i'), $ip);
                Email::send($notifyEmail, 'Organizador', 'Nueva inscripción — ' . $event['title'], $html);
            } catch (\Throwable $e) {
                ErrorHandler::log('Error enviando notificación al organizador ' . $notifyEmail . ': ' . $e->getMessage());
            }
        }

        $successMsg = $formSettings['success_message'] ?? '¡Gracias! Tu inscripción fue recibida.';
        Session::flash('success_message', $successMsg);
        Session::flash('event_title', $event['title']);

        header('Location: ' . APP_URL . '/inscripcion/confirmacion?event=' . urlencode($slug));
        exit;
    }

    /**
     * GET /inscripcion/confirmacion
     */
    public function confirmation(array $params = []): void
    {
        $globalSettings = $this->getSettings();

        $this->render('public/success', [
            'successMessage' => Session::getFlash('success_message') ?? '¡Tu inscripción fue recibida!',
            'eventTitle'     => Session::getFlash('event_title') ?? '',
            'slug'           => $_GET['event'] ?? '',
            'footerText'     => $globalSettings['footer_text'] ?? '',
            'pageTitle'      => 'Inscripción confirmada',
        ], 'public');
    }

    private function getSettings(): array
    {
        try {
            $stmt = Database::getInstance()->query(
                "SELECT key_name, value_data FROM settings"
            );
            $rows = $stmt->fetchAll();
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['key_name']] = $row['value_data'];
            }
            return $settings;
        } catch (\Throwable) {
            return [];
        }
    }

    private function render(string $view, array $data = [], string $layout = 'public'): void
    {
        extract($data);
        $content = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        include VIEWS_PATH . '/layouts/' . $layout . '.php';
    }
}
