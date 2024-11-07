<?php

namespace App\Enums;

enum TransactionTypes: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';
}
