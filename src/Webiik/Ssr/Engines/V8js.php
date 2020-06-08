<?php
declare(strict_types=1);

namespace Webiik\Ssr\Engines;

class V8js implements EngineInterface
{
    public function render(string $uid, string $uiJs, string $envFile = '', string $envJs = ''): string
    {
        // Default result
        $res = '';

        // Instantiate V8Js extension
        $v8js = new \V8Js();

        // Execute JS
        try {
            $v8js->executeString($envJs);
            $res .= $v8js->executeString($uiJs);
        } catch (\V8JsScriptException $e) {
            echo '------- V8JS script execution error -------<br/>';
            echo 'File: ' . $envFile . '<br/>';
            echo 'Line: ' . $e->getJsLineNumber() . '<br/>';
            echo 'Column: ' . $e->getJsStartColumn() . '<br/>';
            echo 'Trace:' . '<br/>';
            print_r($e->getJsTrace());
            echo '<hr/>';
            throw new \Exception('V8JS script execution error.');
        }

        return  $res;
    }
}