<?php

namespace App\Common;

interface RandomDataGenerator
{
    public function createUniqId(string $additionalData = ''): string;
}
