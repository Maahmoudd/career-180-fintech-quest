<?php

namespace App\Enums;

enum ProviderOutcome: string
{
    case Succeeded = 'succeeded';
    case FailedPermanent = 'failed_permanent';
    case FailedTemporary = 'failed_temporary';
    case Unknown = 'unknown';
}
