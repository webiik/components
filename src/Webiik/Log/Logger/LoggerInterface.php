<?php
declare(strict_types=1);

namespace Webiik\Log\Logger;

use Webiik\Log\Message;

interface LoggerInterface
{
    /**
     * Process Message
     * @param Message $message
     */
    public function write(Message $message): void;
}
