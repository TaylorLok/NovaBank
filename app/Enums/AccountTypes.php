<?php

namespace App\Enums;

enum AccountTypes: string
{
    case SAVINGS = 'Savings';
    case CHECKING = 'Check';
    case CREDIT = 'Credit';
}
