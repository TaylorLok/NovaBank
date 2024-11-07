<?php

namespace App\Enums;

enum AccountTypes: string
{
    case SAVINGS = 'savings';
    case CHECKING = 'checking';
    case CREDIT = 'credit';
}
