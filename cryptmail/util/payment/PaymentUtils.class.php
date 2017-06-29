<?php
namespace cryptmail\util\payment;

abstract class PaymentUtils
{
    public static function isCreditCardNumberValid($number)
    {
        $n = strlen($number);
        $digits = array();
        for ($k = 0;$k < $n;$k++)
            $digits[$k] = intval($number[$k]);

        $sum = 0;
        while ($n > 0)
        {
            $sum += $digits[$n - 1];
            $n--;
            if ($n > 0)
            {
                $digit = 2 * $digits[$n - 1];
                $sum += $digit > 9 ? $digit - 9 : $digit;
                $n--;
            }
        }
        return $sum % 10 == 0;
    }
}
?>
