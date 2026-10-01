<?php

declare(strict_types=1);

namespace BuyProxy\Exceptions;

/** 404 — the plan, customer or transaction doesn't exist on this account. */
class NotFoundException extends BuyProxyException
{
}
