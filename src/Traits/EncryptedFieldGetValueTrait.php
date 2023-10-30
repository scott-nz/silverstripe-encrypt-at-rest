<?php

namespace Madmatt\EncryptAtRest\Traits;

trait EncryptedFieldGetValueTrait
{

    public function getValue()
    {
        // Type hardening for PHP 8.1+
        $value = (string)$this->value;

        return $this->getDecryptedValue($value);
    }

}
