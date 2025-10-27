<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PAID = 'paid';
    case UNPAID = 'unpaid';
    case OPEN = 'open';
    case COMPLETE = 'complete';
    case EXPIRED = 'expired';
}
