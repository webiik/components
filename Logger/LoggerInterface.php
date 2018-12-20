<?php
declare(strict_types=1);

namespace Webiik\Log\Logger;

interface LoggerInterface
{
    /**
     * Process log record(s)
     * @param array $records
     */
    public function process(array $records): void;
}
