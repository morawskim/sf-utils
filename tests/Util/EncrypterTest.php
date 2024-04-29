<?php

namespace mmo\sf\tests\Util;

use mmo\sf\Util\Encrypter;
use PHPUnit\Framework\TestCase;

/**
 * @requires extension openssl
 */
class EncrypterTest extends TestCase
{
    public function testEncryptDecrypt(): void
    {
        $text = 'foo';
        $encrypter = new Encrypter('secretkey');
        $encryptedText = $encrypter->encryptString($text);

        $this->assertSame($text, $encrypter->decryptString($encryptedText));
    }
}
