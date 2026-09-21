<?php

namespace App\Enums;

enum PayoutStatus: string
{
    case Pending = 'pending';
    case Reserved = 'reserved';
    case Submitting = 'submitting';
    case Succeeded = 'succeeded';
    case FailedRetryable = 'failed_retryable';
    case FailedPermanent = 'failed_permanent';
    case Unknown = 'unknown';
    case Reconciling = 'reconciling';
    case Refunded = 'refunded';
}
