<?php

namespace App\Enums;

enum PaymentAttemptStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case ERROR = 'error';
}
