<?php
/**
 * Seeder de datos de prueba
 * Ejecutar desde la línea de comandos: php database/seeder.php
 * O desde el navegador en desarrollo: http://localhost/parlamentos/database/seeder.php
 */

// Cargar configuración base
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Conexión directa a la base de datos
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_PORT'] ?? '3306',
    $_ENV['DB_NAME'] ?? 'inscripciones_db'
);

try {
    $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage() . "\n");
}

echo "Iniciando seeder...\n";

// =====================================================
// Crear usuario admin de prueba
// =====================================================
$password = password_hash('Admin@2025!', PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $pdo->prepare("
    INSERT INTO users (name, email, password, role, is_active, must_change_password)
    VALUES (:name, :email, :password, :role, 1, 0)
    ON DUPLICATE KEY UPDATE id = id
");

$stmt->execute([
    ':name'     => 'Administrador Demo',
    ':email'    => 'demo@sistema.com',
    ':password' => $password,
    ':role'     => 'admin',
]);

$adminId = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM users WHERE email = 'demo@sistema.com'")->fetchColumn();
echo "Usuario demo creado (demo@sistema.com / Admin@2025!)\n";

// =====================================================
// Crear eventos de prueba
// =====================================================
$eventos = [
    [
        'title'            => 'Conferencia de Tecnología 2025',
        'slug'             => 'conferencia-tecnologia-2025',
        'description'      => '<p>Una conferencia dedicada a las últimas tendencias en tecnología, inteligencia artificial y desarrollo de software. Contará con speakers internacionales y talleres prácticos.</p><p>El evento se realizará en el Centro de Convenciones de Buenos Aires, con capacidad para 500 asistentes.</p>',
        'location'         => 'Centro de Convenciones, Buenos Aires',
        'start_date'       => date('Y-m-d H:i:s', strtotime('+15 days 09:00')),
        'end_date'         => date('Y-m-d H:i:s', strtotime('+15 days 18:00')),
        'max_capacity'     => 200,
        'status'           => 'published',
        'notify_email'     => 'organizador@ejemplo.com',
        'meta_description' => 'Conferencia de tecnología 2025 en Buenos Aires. IA, desarrollo web y más.',
    ],
    [
        'title'            => 'Taller de Programación Web',
        'slug'             => 'taller-programacion-web',
        'description'      => '<p>Taller intensivo de 8 horas sobre desarrollo web moderno con PHP, JavaScript y bases de datos. Ideal para principiantes y desarrolladores que quieran actualizar sus conocimientos.</p>',
        'location'         => 'Aula Magna, Universidad Tecnológica',
        'start_date'       => date('Y-m-d H:i:s', strtotime('+30 days 10:00')),
        'end_date'         => date('Y-m-d H:i:s', strtotime('+30 days 18:00')),
        'max_capacity'     => 30,
        'status'           => 'published',
        'notify_email'     => 'taller@ejemplo.com',
        'meta_description' => 'Taller de programación web PHP y JavaScript.',
    ],
    [
        'title'            => 'Evento Borrador — No Publicado',
        'slug'             => 'evento-borrador-prueba',
        'description'      => '<p>Este es un evento en estado borrador, no visible en el listado público.</p>',
        'location'         => 'Por confirmar',
        'start_date'       => date('Y-m-d H:i:s', strtotime('+60 days 09:00')),
        'end_date'         => date('Y-m-d H:i:s', strtotime('+60 days 17:00')),
        'max_capacity'     => null,
        'status'           => 'draft',
        'notify_email'     => 'draft@ejemplo.com',
        'meta_description' => '',
    ],
];

$eventoIds = [];
$stmtEvento = $pdo->prepare("
    INSERT INTO events (user_id, title, slug, description, location, start_date, end_date, max_capacity, status, notify_email, meta_description)
    VALUES (:user_id, :title, :slug, :description, :location, :start_date, :end_date, :max_capacity, :status, :notify_email, :meta_description)
    ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)
");

foreach ($eventos as $evento) {
    $stmtEvento->execute(array_merge([':user_id' => $adminId], $evento));
    $eventoIds[] = $pdo->lastInsertId();
    echo "Evento creado: {$evento['title']}\n";
}

// =====================================================
// Crear formularios para los eventos
// =====================================================
$formularioConferencia = json_encode([
    'version'  => '1.0',
    'settings' => [
        'submit_label'    => 'Confirmar inscripción',
        'success_message' => '¡Gracias! Tu inscripción a la Conferencia de Tecnología fue recibida. Te enviaremos un email con los detalles.',
        'notify_email'    => 'organizador@ejemplo.com',
        'max_submissions' => null,
        'allow_duplicates' => false,
    ],
    'fields' => [
        [
            'id'          => 'field_' . bin2hex(random_bytes(8)),
            'type'        => 'text',
            'label'       => 'Nombre completo',
            'placeholder' => 'Ej: Juan Pérez',
            'required'    => true,
            'help_text'   => '',
            'width'       => 'half',
            'order'       => 1,
            'validations' => ['min_length' => 3, 'max_length' => 100],
        ],
        [
            'id'          => 'field_' . bin2hex(random_bytes(8)),
            'type'        => 'email',
            'label'       => 'Correo electrónico',
            'placeholder' => 'tucorreo@ejemplo.com',
            'required'    => true,
            'help_text'   => 'Recibirás la confirmación en este email',
            'width'       => 'half',
            'order'       => 2,
            'validations' => [],
        ],
        [
            'id'          => 'field_' . bin2hex(random_bytes(8)),
            'type'        => 'tel',
            'label'       => 'Teléfono de contacto',
            'placeholder' => '+54 11 1234-5678',
            'required'    => false,
            'help_text'   => '',
            'width'       => 'half',
            'order'       => 3,
            'validations' => [],
        ],
        [
            'id'          => 'field_' . bin2hex(random_bytes(8)),
            'type'        => 'select',
            'label'       => 'Tipo de entrada',
            'required'    => true,
            'help_text'   => '',
            'width'       => 'half',
            'order'       => 4,
            'options'     => [
                ['value' => 'general',     'label' => 'General (gratuita)'],
                ['value' => 'vip',         'label' => 'VIP (con acceso a workshops)'],
                ['value' => 'estudiante',  'label' => 'Estudiante (con DNI)'],
            ],
        ],
        [
            'id'          => 'field_' . bin2hex(random_bytes(8)),
            'type'        => 'select',
            'label'       => '¿Cómo te enteraste del evento?',
            'required'    => false,
            'help_text'   => '',
            'width'       => 'half',
            'order'       => 5,
            'options'     => [
                ['value' => 'redes',    'label' => 'Redes sociales'],
                ['value' => 'email',    'label' => 'Email / newsletter'],
                ['value' => 'amigo',    'label' => 'Un amigo o colega'],
                ['value' => 'web',      'label' => 'Búsqueda en internet'],
                ['value' => 'otro',     'label' => 'Otro'],
            ],
        ],
        [
            'id'          => 'field_' . bin2hex(random_bytes(8)),
            'type'        => 'checkbox',
            'label'       => '¿Qué temas te interesan?',
            'required'    => false,
            'help_text'   => 'Podés seleccionar más de uno',
            'width'       => 'full',
            'order'       => 6,
            'options'     => [
                ['value' => 'ia',        'label' => 'Inteligencia Artificial'],
                ['value' => 'web',       'label' => 'Desarrollo Web'],
                ['value' => 'mobile',    'label' => 'Desarrollo Mobile'],
                ['value' => 'devops',    'label' => 'DevOps / Cloud'],
                ['value' => 'seguridad', 'label' => 'Ciberseguridad'],
            ],
        ],
        [
            'id'          => 'field_' . bin2hex(random_bytes(8)),
            'type'        => 'textarea',
            'label'       => 'Comentarios adicionales',
            'placeholder' => 'Alguna consulta o comentario...',
            'required'    => false,
            'help_text'   => '',
            'width'       => 'full',
            'order'       => 7,
            'validations' => ['max_length' => 500],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$formularioTaller = json_encode([
    'version'  => '1.0',
    'settings' => [
        'submit_label'    => 'Reservar mi lugar',
        'success_message' => '¡Lugar reservado! Te enviaremos un email con los detalles del taller.',
        'notify_email'    => 'taller@ejemplo.com',
        'max_submissions' => 30,
        'allow_duplicates' => false,
    ],
    'fields' => [
        [
            'id'          => 'field_' . bin2hex(random_bytes(8)),
            'type'        => 'text',
            'label'       => 'Nombre y apellido',
            'placeholder' => 'Ej: María García',
            'required'    => true,
            'help_text'   => '',
            'width'       => 'half',
            'order'       => 1,
            'validations' => ['min_length' => 3, 'max_length' => 100],
        ],
        [
            'id'          => 'field_' . bin2hex(random_bytes(8)),
            'type'        => 'email',
            'label'       => 'Email',
            'placeholder' => 'tucorreo@ejemplo.com',
            'required'    => true,
            'help_text'   => '',
            'width'       => 'half',
            'order'       => 2,
            'validations' => [],
        ],
        [
            'id'          => 'field_' . bin2hex(random_bytes(8)),
            'type'        => 'select',
            'label'       => 'Nivel de experiencia',
            'required'    => true,
            'help_text'   => '',
            'width'       => 'full',
            'order'       => 3,
            'options'     => [
                ['value' => 'principiante', 'label' => 'Principiante (sin experiencia previa)'],
                ['value' => 'intermedio',   'label' => 'Intermedio (algo de experiencia)'],
                ['value' => 'avanzado',     'label' => 'Avanzado (trabajo con código)'],
            ],
        ],
        [
            'id'          => 'field_' . bin2hex(random_bytes(8)),
            'type'        => 'radio',
            'label'       => '¿Traés laptop?',
            'required'    => true,
            'help_text'   => 'El taller es práctico, se recomienda traer computadora',
            'width'       => 'full',
            'order'       => 4,
            'options'     => [
                ['value' => 'si',  'label' => 'Sí, traigo mi computadora'],
                ['value' => 'no',  'label' => 'No, necesito una prestada'],
            ],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$stmtForm = $pdo->prepare("
    INSERT INTO forms (event_id, title, fields_json, is_active)
    VALUES (:event_id, :title, :fields_json, 1)
");

$stmtForm->execute([
    ':event_id'    => $eventoIds[0],
    ':title'       => 'Formulario Conferencia de Tecnología 2025',
    ':fields_json' => $formularioConferencia,
]);
$formId1 = $pdo->lastInsertId();

$stmtForm->execute([
    ':event_id'    => $eventoIds[1],
    ':title'       => 'Formulario Taller de Programación Web',
    ':fields_json' => $formularioTaller,
]);
$formId2 = $pdo->lastInsertId();

echo "Formularios creados\n";

// =====================================================
// Crear inscripciones de prueba
// =====================================================
$formConf   = json_decode($formularioConferencia, true);
$fieldIds1  = array_column($formConf['fields'], 'id');

$nombres   = ['Ana Martínez', 'Carlos López', 'Sofía Rodríguez', 'Diego Fernández', 'Valentina García'];
$emails    = ['ana@mail.com', 'carlos@mail.com', 'sofia@mail.com', 'diego@mail.com', 'vale@mail.com'];
$entradas  = ['general', 'vip', 'estudiante', 'general', 'vip'];
$fuentes   = ['redes', 'email', 'amigo', 'web', 'redes'];

$stmtSub = $pdo->prepare("
    INSERT INTO submissions (form_id, event_id, response_data, status, ip_address, submitted_at)
    VALUES (:form_id, :event_id, :response_data, :status, :ip_address, :submitted_at)
");

for ($i = 0; $i < 5; $i++) {
    $responseData = [];
    if (isset($fieldIds1[0])) $responseData[$fieldIds1[0]] = $nombres[$i];
    if (isset($fieldIds1[1])) $responseData[$fieldIds1[1]] = $emails[$i];
    if (isset($fieldIds1[2])) $responseData[$fieldIds1[2]] = '+54 11 ' . rand(1000, 9999) . '-' . rand(1000, 9999);
    if (isset($fieldIds1[3])) $responseData[$fieldIds1[3]] = $entradas[$i];
    if (isset($fieldIds1[4])) $responseData[$fieldIds1[4]] = $fuentes[$i];

    $stmtSub->execute([
        ':form_id'       => $formId1,
        ':event_id'      => $eventoIds[0],
        ':response_data' => json_encode($responseData),
        ':status'        => ['pending', 'confirmed', 'confirmed', 'pending', 'cancelled'][$i],
        ':ip_address'    => '192.168.1.' . ($i + 10),
        ':submitted_at'  => date('Y-m-d H:i:s', strtotime('-' . ($i * 2) . ' days')),
    ]);
}

echo "5 inscripciones de prueba creadas para el primer evento\n";
echo "\n✅ Seeder completado exitosamente.\n";
echo "   Credenciales de acceso:\n";
echo "   Super Admin: admin@sistema.com / Admin@2025!\n";
echo "   Admin Demo:  demo@sistema.com  / Admin@2025!\n";
