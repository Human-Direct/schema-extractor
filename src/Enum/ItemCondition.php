<?php

declare(strict_types=1);

namespace HumanDirect\SchemaExtractor\Enum;

enum ItemCondition: string
{
    case DamagedCondition = 'https://schema.org/DamagedCondition';
    case NewCondition = 'https://schema.org/NewCondition';
    case RefurbishedCondition = 'https://schema.org/RefurbishedCondition';
    case UsedCondition = 'https://schema.org/UsedCondition';
}
