<?php

namespace App\Enums;

enum LedgerEntryType: string
{
    case Earned = 'earned';
    case PlatformFee = 'platform_fee';
    case Refund = 'refund';
    case Clawback = 'clawback';
    case Payout = 'payout';
    case PayoutReversal = 'payout_reversal';
    case Adjustment = 'adjustment';
}
