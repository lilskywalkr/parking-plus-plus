<?php

namespace App\Enums;

enum ParkingRecordActionEnum: string
{
    case BLOCKED = 'blocked';
    case UNBLOCKED = 'unblocked';
    case DRIVE_IN = 'drive in';
    case DRIVE_OUT = 'drive out';
}
