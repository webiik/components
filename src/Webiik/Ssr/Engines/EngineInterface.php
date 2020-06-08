<?php
declare(strict_types=1);

namespace Webiik\Ssr\Engines;

interface EngineInterface {
    /**
     * Process JS:
     * 1. Initiate JS environment from $jsEnv
     * 2. Return result of $jsUi
     *
     * Helpers:
     * $uid - Unique identifier of JS component
     * $envFile - Filename of file which from $jsEnv is loaded (incl. path)
     *
     * @param string $uid
     * @param string $uiJs
     * @param string $envFile
     * @param string $envJs
     * @return string
     */
    public function render(string $uid, string $uiJs, string $envFile = '', string $envJs = ''): string;
}
