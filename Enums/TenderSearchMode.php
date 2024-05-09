<?php

namespace DiplomaProject\Enums;

enum TenderSearchMode: string
{
    case simple   = 'full-text'; // FT
    case targeted = 'publication-number';
}
