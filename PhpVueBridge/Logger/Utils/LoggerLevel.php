<?php

namespace PhpVueBridge\Logger\Utils;

class LoggerLevel
{
    const ERROR = 'error';

    const WARNING = 'warning';

    const EMERGENCY = 'emergency';

    const INFO = 'info';

    const NOTICE = 'notice';

    const DEBUG = 'debug';

    const CRITICAL = 'critical';

    const ALERT = 'alert';

    public const OUTMAP = [
        self::EMERGENCY => STDERR,
        self::ALERT => STDERR,
        self::CRITICAL => STDERR,
        self::ERROR => STDERR,

        self::WARNING => STDOUT,
        self::NOTICE => STDOUT,
        self::INFO => STDOUT,
        self::DEBUG => STDOUT,
    ];

    public const LOGFILE = [
        self::EMERGENCY,
        self::ALERT,
        self::CRITICAL,
        self::ERROR,
    ];
}
?>