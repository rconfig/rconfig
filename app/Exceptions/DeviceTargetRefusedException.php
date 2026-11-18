<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A device target that rConfig will not dial: it resolves to this server, or it
 * does not resolve at all. The message is safe to log and show to users.
 */
class DeviceTargetRefusedException extends RuntimeException {}
