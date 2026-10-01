<?php

declare(strict_types=1);

namespace BuyProxy\Exceptions;

/** The request never got a response (DNS, reset, timeout, offline). */
class ConnectionException extends BuyProxyException
{
}
