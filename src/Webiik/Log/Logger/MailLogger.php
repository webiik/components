<?php
declare(strict_types=1);

namespace Webiik\Log\Logger;

use Webiik\Log\Record;

class MailLog implements LoggerInterface
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
            /** @var Record $record */
            $data = $record->getData();
            $htmlMsg = $this->htmlError(
                $data['errType'],
                $data['errMsg'],
                $data['file'],
                $data['line'],
                $data['trace']
            );
            echo $record->getMessage();
            echo $htmlMsg;
            //$rows = $rows . $this->parse($record);
        }
    }

    /**
     * Return formatted error to html
     * @param string $errType
     * @param string $message
     * @param string $file
     * @param int $line
     * @param array $trace
     * @return string
     */
    private function htmlError(string $errType, string $message, string $file, int $line, array $trace): string
    {
        $html = '<h1>' . $errType . '</h1>';
        $html .= '<b>' . $message . '</b><br/><br/>';
        $pos = strrpos($file, '/');
        $html .= substr($file, 0, $pos + 1) . '<b>' . substr($file, $pos + 1, strlen($file)) . '</b> ';
        $html .= '(on line: <b>' . $line . '</b>)<br/><br/>';
        if (count($trace) > 0) {
            $html .= 'Trace:<br/>';
            foreach ($trace as $traceLine) {
                $html .= $traceLine . '<br/>';
            }
        }
        return $html;
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
