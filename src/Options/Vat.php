<?php

declare(strict_types=1);

namespace netFantom\RobokassaApi\Options;

use JsonSerializable;

class Vat implements JsonSerializable
{
    use JsonSerializeMethod;

    public readonly string $sum;

    public function __construct(
        public readonly Tax $type,
        float|string $sum
    ) {
        $this->sum = number_format(num: (float)$sum, decimals: 2, thousands_separator: '');
    }
}
