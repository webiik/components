<?php
declare(strict_types=1);

namespace Webiik\View\Renderer;

class Twig implements RendererInterface
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * Twig constructor.
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return \Twig_Environment
     */
    public function core()
    {
        return $this->twig;
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render(string $template, array $data = []): string
    {
        try {
            return $this->twig->render($template, $data);
        } catch (\Exception $exception) {
            return '';
        }
    }
}
