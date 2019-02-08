<?php
declare(strict_types=1);

namespace Webiik\View;

use Webiik\View\Renderer\RendererInterface;

class View
{
    /**
     * @var callable|RendererInterface
     */
    private $renderer;

    public function setRenderer(callable $factory):void
    {
        $this->renderer = $factory;
    }

    /**
     * Get object of underlying template engine
     * @return object
     */
    public function getTemplateEngine()
    {
        return $this->getRenderer()->core();
    }

    /**
     * Render template to string
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render(string $template, array $data = []): string
    {
        return $this->getRenderer()->render($template, $data);
    }

    /**
     * Get renderer instance
     * @return RendererInterface
     */
    private function getRenderer(): RendererInterface
    {
        // Instantiate mailer only once
        if (is_callable($this->renderer)) {
            $renderer = $this->renderer;
            $this->renderer = $renderer();
        }

        return $this->renderer;
    }
}
