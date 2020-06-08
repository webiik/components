<?php
declare(strict_types=1);

namespace Webiik\View\Renderer;

class Twig implements RendererInterface
{
    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * Twig constructor.
     * @param \Twig\Environment $twig
     */
    public function __construct(\Twig\Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return \Twig\Environment
     */
    public function core()
    {
        return $this->twig;
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data);
    }
}
