<?php
/**
 * Paginador reutilizable
 * Uso:
 *   $paginator = new Paginator($total, 20, $_GET['page'] ?? 1);
 *   $offset    = $paginator->offset();
 *   echo $paginator->render();
 */

class Paginator
{
    private int $totalItems;
    private int $itemsPerPage;
    private int $currentPage;
    private int $totalPages;

    public function __construct(int $totalItems, int $itemsPerPage = 20, int $currentPage = 1)
    {
        $this->totalItems   = max(0, $totalItems);
        $this->itemsPerPage = max(1, $itemsPerPage);
        $this->totalPages   = (int) ceil($this->totalItems / $this->itemsPerPage);
        $this->currentPage  = max(1, min($currentPage, max(1, $this->totalPages)));
    }

    public function offset(): int
    {
        return ($this->currentPage - 1) * $this->itemsPerPage;
    }

    public function limit(): int
    {
        return $this->itemsPerPage;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    public function totalPages(): int
    {
        return $this->totalPages;
    }

    public function totalItems(): int
    {
        return $this->totalItems;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    /**
     * Genera los links de paginación preservando los query params actuales
     */
    public function render(string $pageParam = 'page'): string
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        // Obtener la URL base preservando todos los query params excepto el de página
        $queryParams = $_GET;
        unset($queryParams[$pageParam]);
        $baseQuery = $queryParams ? '?' . http_build_query($queryParams) . '&' : '?';

        $html = '<nav class="flex items-center justify-between mt-6" aria-label="Paginación">';
        $html .= '<div class="text-sm text-gray-600">Mostrando ' . $this->firstItem() . '–' . $this->lastItem() . ' de ' . number_format($this->totalItems, 0, ',', '.') . ' resultados</div>';
        $html .= '<div class="flex items-center gap-1">';

        // Página anterior
        if ($this->hasPreviousPage()) {
            $prevPage = $this->currentPage - 1;
            $html .= '<a href="' . $baseQuery . $pageParam . '=' . $prevPage . '" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 text-gray-600">‹ Anterior</a>';
        }

        // Páginas numeradas (con elipsis)
        $pages = $this->getPageRange();
        foreach ($pages as $page) {
            if ($page === '...') {
                $html .= '<span class="px-3 py-1 text-sm text-gray-400">…</span>';
            } elseif ($page === $this->currentPage) {
                $html .= '<span class="px-3 py-1 rounded border border-indigo-500 bg-indigo-600 text-white text-sm font-medium">' . $page . '</span>';
            } else {
                $html .= '<a href="' . $baseQuery . $pageParam . '=' . $page . '" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 text-gray-600">' . $page . '</a>';
            }
        }

        // Página siguiente
        if ($this->hasNextPage()) {
            $nextPage = $this->currentPage + 1;
            $html .= '<a href="' . $baseQuery . $pageParam . '=' . $nextPage . '" class="px-3 py-1 rounded border border-gray-300 text-sm hover:bg-gray-50 text-gray-600">Siguiente ›</a>';
        }

        $html .= '</div></nav>';

        return $html;
    }

    private function firstItem(): int
    {
        if ($this->totalItems === 0) return 0;
        return $this->offset() + 1;
    }

    private function lastItem(): int
    {
        return min($this->offset() + $this->itemsPerPage, $this->totalItems);
    }

    /**
     * Genera el rango de páginas con elipsis para no mostrar todas cuando hay muchas
     */
    private function getPageRange(): array
    {
        $pages   = [];
        $current = $this->currentPage;
        $total   = $this->totalPages;
        $delta   = 2; // páginas a mostrar a cada lado de la actual

        $rangeStart = max(2, $current - $delta);
        $rangeEnd   = min($total - 1, $current + $delta);

        $pages[] = 1;

        if ($rangeStart > 2) {
            $pages[] = '...';
        }

        for ($i = $rangeStart; $i <= $rangeEnd; $i++) {
            $pages[] = $i;
        }

        if ($rangeEnd < $total - 1) {
            $pages[] = '...';
        }

        if ($total > 1) {
            $pages[] = $total;
        }

        return $pages;
    }
}
