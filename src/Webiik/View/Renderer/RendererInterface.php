<?php
declare(strict_types=1);

namespace Webiik\View\Renderer;

interface RendererInterface
{
    /**
     * Return object of underlying template engine
     * @return mixed
     */
    public function core();

    /**
     * Return rendered template as a string
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render(string $template, array $data): string;
}
