<?php

namespace DiplomaProject\Enums;

enum TenderSearchScope: string
{
    case ALL    = 'ALL';
    case ACTIVE = 'ACTIVE';
    case LATEST = 'LATEST';
}
