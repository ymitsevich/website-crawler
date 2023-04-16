<?php

namespace App\Common;

class SimpleRandomDataGenerator implements RandomDataGenerator
{
    public function createUniqId(string $additionalData = ''): string
    {
        return uniqid($additionalData, true);
    }
}
