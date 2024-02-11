<?php

namespace mmo\sf\Translation;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Silly implementation of TranslatorInterface for unit tests.
 *
 * The message-id will be prefixed with the string passed to the constructor.
 */
class FakeTranslator implements TranslatorInterface
{
    /**
     * @var string
     */
    private $locale;

    public function __construct(string $locale)
    {
        $this->locale = $locale;
    }

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        $prefix = $locale ?? $this->locale;

        return sprintf("%s-%s", $prefix, $id);
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
