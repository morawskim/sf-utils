<?php

namespace mmo\sf\tests\data;

use MyCLabs\Enum\Enum;

/**
 * @method static static DRAFT()
 * @method static static PUBLISHED()
 */
class StatusEnum extends Enum
{
    private const DRAFT = 'draft';
    private const PUBLISHED = 'published';
}
