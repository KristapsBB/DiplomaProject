<?php

namespace DiplomaProject\Models;

class TenderList
{
    private $total_tenders_count = 0;

    public function __construct(private array $tenders = [])
    {
        $this->total_tenders_count = count($tenders);
    }

    public function getTenders(): array
    {
        return $this->tenders;
    }

    public function isEmpty(): bool
    {
        return ($this->total_tenders_count <= 0);
    }
}
