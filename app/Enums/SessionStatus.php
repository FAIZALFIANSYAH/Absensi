<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
}
