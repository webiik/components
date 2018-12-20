<?php
declare(strict_types=1);

namespace Webiik\Log\Logger;

use Webiik\Log\Record;

class Mail implements LoggerInterface
{
    /**
     * Default message format
     * @var string
     */
    private $messageFormat = "{date} {time} [{level}] {message}";

    /**
     * @param array $records
     */
    public function process(array $records): void
    {
        $rows = '';

        foreach ($records as $record) {
            /* @var $record Record */
            $rows = $rows . $this->parse($record);
        }
    }

    /**
     * Parse message by message format
     * @param Record $record
     * @return string
     */
    private function parse(Record $record): string
    {
        return strtr($this->messageFormat, [
            '{date}' => date('Y/m/d', $record->getTime()),
            '{time}' => date('H:i:s', $record->getTime()),
            '{level}' => $record->getLevel(),
            '{message}' => $record->getMessage(),
        ]);
    }
}
