<?php
declare(strict_types=1);

namespace Webiik\Translation;

class TranslationInjector
{
    /**
     * @var callable
     */
    private $factory;

    public function __construct(callable $factory)
    {
        $this->factory = $factory;
    }

    public function __invoke(): array
    {
        return ($this->factory)();
    }
}
