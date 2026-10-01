<?php

declare(strict_types=1);

namespace BuyProxy\Exceptions;

/** 402 — empty reseller pool, or a plan with no GB left. */
class InsufficientBandwidthException extends BuyProxyException
{
}
