<?php
namespace cryptmail\crypto;

abstract class CryptoUtils
{
    public static function bcToHex($input)
    {
        return self::bcBaseConv($input, 10, 16);
    }
    public static function bcBaseConv($input, $inBase, $outBase)
    {
        if ($inBase < 0 || $inBase > 36 || $outBase < 0 || $outBase > 36)
        {
            throw new Exception("Base must be positive and <= 36");
        }
        $base = \str_split("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ");
        $input = strToUpper($input);
        $j = strlen($input);
        $value = "";
        // convert to base 10
        if ($inBase != 10)
        {
            for ($n = 0;$n < $j;$n++)
            {
                $d = "" . \bcpow($inBase, $j - $n - 1);
                $f = \array_search($input[$n], $base);
                $value = \bcadd($value, bcmul($f, $d));
            }
        }
        else
        {
            $value = $input;
        }
        if ($outBase == 10)
        {
            return $value;
        }
        $result = "";
        $j = $inBase < $outBase? $j : $j * ($inBase - $outBase);
        for ($n = $j;$n >= 0;$n--)
        {
            // calculate the value of this hex digit
            $dValue = \bcpow($outBase, $n);
            if (\bccomp($dValue, $value) <= 0)
            { // this hex digit is contained in the string
                $k = \bcmod($value, $dValue);
                $s = \bcsub($value, $k);
                // is assured to be integer and single-digit
                $r = \bcdiv($s, $dValue);
                $value = \bcsub($value, $s);
                $result .= $base[$r];
            }
            else if (!empty($result))
            { // if this is not a preceeding empty digit, append 0s
                $result .= "0";
            }
        }
        return $result;
    }
    public static function toHexString($message)
    {
        $str = "";
        $j = strlen($message);
        for ($i = 0;$i < $j;$i++)
        {
            $d = dechex(ord($message[$i]));
            if (strlen($d) == 1)
            {
                $d = "0". $d;
            }
            $str .= $d;
        }
        return $str;
    }
    public static function fromHexString($hex)
    {
        $k = strlen($hex);
        $result = "";
        for ($n = 0;$n < $k;)
        {
            $cur = $hex[$n++];
            if ($n < $k)
            {
                $cur .= $hex[$n++];
            }
            $result .= chr(intval($cur, 16));
        }
        return $result;
    }
}