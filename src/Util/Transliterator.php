<?php

namespace mmo\sf\Util;

use RuntimeException;

/**
 * Based on yii2 code
 *
 * @link https://github.com/yiisoft/yii2/blob/master/framework/helpers/BaseInflector.php#L512
 */
class Transliterator
{
    /**
     * Shortcut for `Any-Latin; NFKD` transliteration rule.
     *
     * The rule is strict, letters will be transliterated with
     * the closest sound-representation chars. The result may contain any UTF-8 chars. For example:
     * `获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` will be transliterated to
     * `huò qǔ dào dochira Ukraí̈nsʹka: g̀,ê, Srpska: đ, n̂, d̂! ¿Español?`.
     *
     * Used in [[transliterate()]].
     * For detailed information see [unicode normalization forms](http://unicode.org/reports/tr15/#Normalization_Forms_Table)
     *
     * @see http://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     */
    public const TRANSLITERATE_STRICT = 'Any-Latin; NFKD';

    /**
     * Shortcut for `Any-Latin; Latin-ASCII` transliteration rule.
     *
     * The rule is medium, letters will be
     * transliterated to characters of Latin-1 (ISO 8859-1) ASCII table. For example:
     * `获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` will be transliterated to
     * `huo qu dao dochira Ukrainsʹka: g,e, Srpska: d, n, d! ¿Espanol?`.
     *
     * Used in [[transliterate()]].
     * For detailed information see [unicode normalization forms](http://unicode.org/reports/tr15/#Normalization_Forms_Table)
     *
     * @see http://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     */
    public const TRANSLITERATE_MEDIUM = 'Any-Latin; Latin-ASCII';

    /**
     * Shortcut for `Any-Latin; Latin-ASCII; [\u0080-\uffff] remove` transliteration rule.
     *
     * The rule is loose,
     * letters will be transliterated with the characters of Basic Latin Unicode Block.
     * For example:
     * `获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` will be transliterated to
     * `huo qu dao dochira Ukrainska: g,e, Srpska: d, n, d! Espanol?`.
     *
     * Used in [[transliterate()]].
     * For detailed information see [unicode normalization forms](http://unicode.org/reports/tr15/#Normalization_Forms_Table)
     *
     * @see http://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     */
    public const TRANSLITERATE_LOOSE = 'Any-Latin; Latin-ASCII; [\u0080-\uffff] remove';

    /**
     * @var mixed Either a [[\Transliterator]], or a string from which a [[\Transliterator]] can be built
     * for transliteration. Used by [[transliterate()]] when intl is available. Defaults to [[TRANSLITERATE_LOOSE]]
     *
     * @see https://secure.php.net/manual/en/transliterator.transliterate.php
     */
    public static $transliterator = self::TRANSLITERATE_LOOSE;

    /**
     * Returns transliterated version of a string.
     *
     * If intl extension isn't available uses fallback that converts latin characters only
     * and removes the rest. You may customize characters map via $transliteration property
     * of the helper.
     *
     * @param string $string input string
     * @param string|\Transliterator $transliterator either a [[\Transliterator]] or a string
     * from which a [[\Transliterator]] can be built.
     *
     * @return string
     */
    public static function transliterate(string $string, $transliterator = null): string
    {
        if (!extension_loaded('intl')) {
            throw new RuntimeException(sprintf('Extension "%s" is not loaded', 'intl'));
        }

        if ($transliterator === null) {
            $transliterator = static::$transliterator;
        }

        return transliterator_transliterate($transliterator, $string);
    }
}
