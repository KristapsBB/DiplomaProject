<?php

namespace DiplomaProject\Models;

class Pagination
{
    public function __construct(
        private int $page,
        private int $total_pages_count,
        private string $base_url,
    ) {
    }

    public function currentPage(): int
    {
        return $this->page;
    }

    public function totalPagesCount(): int
    {
        if ($this->total_pages_count > 50) {
            return 50;
        }

        return $this->total_pages_count;
    }

    public function getPageUrl(int $page): string
    {
        return "{$this->base_url}{$page}";
    }

    public function isCurrent(int $page): bool
    {
        return ($this->page === $page);
    }

    public function getLinks(
        string $link_css_class = 'pagination__nav-link',
        string $active_css_class = 'pagination__nav-link_active'
    ): array {
        $links = [];

        for ($page = 1; $page <= $this->totalPagesCount(); $page++) {
            if ($this->isCurrent($page)) {
                $links[] = "<a class='$link_css_class $active_css_class' href='{$this->getPageUrl($page)}'>$page</a>";
            } else {
                $links[] = "<a class='$link_css_class' href='{$this->getPageUrl($page)}'>$page</a>";
            }
        }

        return $links;
    }
}
