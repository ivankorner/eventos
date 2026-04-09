<?php
/**
 * Controlador del dashboard administrativo
 */

class DashboardController
{
    private EventModel $eventModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
    }

    /**
     * GET /admin/dashboard
     */
    public function index(array $params = []): void
    {
        $metrics    = $this->eventModel->getDashboardMetrics();
        $chartData  = $this->eventModel->getSubmissionsChart();
        $recentSubs = $this->eventModel->getRecentSubmissions(10);

        // Preparar datos del gráfico en formato esperado por Chart.js
        $chartLabels = [];
        $chartValues = [];

        // Generar los últimos 30 días aunque no tengan datos
        for ($i = 29; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $chartLabels[] = date('d/m', strtotime($day));

            // Buscar si existe dato para ese día
            $found = array_filter($chartData, fn($r) => $r['day'] === $day);
            $chartValues[] = $found ? array_values($found)[0]['total'] : 0;
        }

        $this->render('admin/dashboard/index', [
            'metrics'       => $metrics,
            'chartLabels'   => json_encode($chartLabels),
            'chartValues'   => json_encode($chartValues),
            'recentSubs'    => $recentSubs,
            'pageTitle'     => 'Dashboard',
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $content = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        include VIEWS_PATH . '/layouts/admin.php';
    }
}
