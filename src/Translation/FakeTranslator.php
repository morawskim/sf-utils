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
    private $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null)
    {
        return sprintf("%s-%s", $this->prefix, $id);
    }
}
