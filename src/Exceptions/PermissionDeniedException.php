<?php

declare(strict_types=1);

namespace BuyProxy\Exceptions;

/** 403 — suspended account or customer, or the plan doesn't include this endpoint. */
class PermissionDeniedException extends BuyProxyException
{
}
