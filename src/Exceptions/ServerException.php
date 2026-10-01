<?php

declare(strict_types=1);

namespace BuyProxy\Exceptions;

/** 5xx — BuyProxy or an upstream provider is unavailable; usually safe to retry. */
class ServerException extends BuyProxyException
{
}
