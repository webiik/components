<?php
declare(strict_types=1);

namespace Webiik\Log\Logger;

interface LoggerInterface
{
    /**
     * Process log record(s)
     * @param array $records Array of Record objects
     */
    public function process(array $records): void;
}
