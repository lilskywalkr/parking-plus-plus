<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case OPEN = 'open';
    case COMPLETE = 'complete';
    case EXPIRED = 'expired';
}
