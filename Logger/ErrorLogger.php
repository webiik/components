<?php
declare(strict_types=1);

namespace Webiik\Log\Logger;

use Webiik\Log\Record;

class ErrorLogger implements LoggerInterface
{
    /**
     * Default message format
     * @var string
     */
    private $messageFormat = "{date} {time} [{level}] {message}\n";

    /**
     * @link http://php.net/manual/en/function.error-log.php
     * @var int
     */
    private $messageType;

    /**
     * @link http://php.net/manual/en/function.error-log.php
     * @var string
     */
    private $destination;

    /**
     * @link http://php.net/manual/en/function.error-log.php
     * @var string
     */
    private $extraHeaders;

    /**
     * @param array $records
     */
    public function process(array $records): void
    {
        $rows = '';

        foreach ($records as $record) {
            /* @var $record Record */
            $rows .= $this->parse($record);
        }

        if (!$this->messageType) {
            error_log($rows);
        } elseif ($this->messageType == 1) {
            error_log($rows, $this->messageType, $this->destination);
        } elseif ($this->messageType == 3 && !$this->extraHeaders) {
            error_log($rows, $this->messageType, $this->destination);
        } elseif ($this->messageType == 3 && $this->extraHeaders) {
            error_log($rows, $this->messageType, $this->destination, $this->extraHeaders);
        } elseif ($this->messageType == 4) {
            error_log($rows, $this->messageType);
        }
    }

    /**
     * @param string $messageFormat
     */
    public function setMessageFormat(string $messageFormat): void
    {
        $this->messageFormat = $messageFormat;
    }

    /**
     * @param int $messageType
     */
    public function setMessageType(int $messageType): void
    {
        $this->messageType = $messageType;
    }

    /**
     * @param string $destination
     */
    public function setDestination(string $destination): void
    {
        $this->destination = $destination;
    }

    /**
     * @param string $extraHeaders
     */
    public function setExtraHeaders(string $extraHeaders): void
    {
        $this->extraHeaders = $extraHeaders;
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
