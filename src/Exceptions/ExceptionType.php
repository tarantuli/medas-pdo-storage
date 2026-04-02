<?php

declare(strict_types=1);

namespace Medas\PdoStorage\Exceptions;

enum ExceptionType
{
    /** Unique constraint violation — a row with these values already exists. */
    case DuplicateKey;

    /** Foreign key constraint violation — referenced row does not exist, or parent row still has children. */
    case ForeignKeyViolation;

    /** The database detected a deadlock and rolled back this transaction so the other could proceed. */
    case DeadlockDetected;

    /** A lock could not be acquired within the configured wait timeout. */
    case LockWaitTimeout;

    /** The database connection was lost mid-request. */
    case ConnectionLost;

    /** The exception type could not be determined from the driver-specific error. */
    case Unknown;
}
