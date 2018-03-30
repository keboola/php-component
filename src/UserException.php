<?php

declare(strict_types=1);

namespace Keboola\Component;

use Exception;

/**
 * Throw this exception whenever an expectation fails and user is able to fix it by supplying different configuration
 * or data. Typical case is invalid parameter in config. Do not use it for any expectation failure, that is out of
 * user's reach. Such case would be when there are insufficient privledges to write a file.
 */
class UserException extends Exception
{
}
