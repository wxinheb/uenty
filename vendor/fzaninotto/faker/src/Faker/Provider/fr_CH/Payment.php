<?php

namespace Faker\Provider\fr_CH;

class Payment extends \Faker\Provider\Payment
{
    
    public static function bankAccountNumber($prefix = '', $countryCode = 'CH', $length = null)
    {
        return static::iban($countryCode, $prefix, $length);
    }
}
