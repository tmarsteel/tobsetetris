<?php
namespace cryptmail\crypto;

class RSAPadding
{    
    public function getMinimumOffset()
    {
        return 4;
    }
    public function pad($data, $targetLength)
    {
        $j = is_array($data) ? count($data) : strlen($data);
        if ($j + 3 > $targetLength)
        {
            throw new Exception("data too long!");
        }
        $l = "" . $j;
        if ($l < 10)
        {
            $l = "00" . $l;
        }
        else if ($l < 100)
        {
            $l = "0" . $l;
        }
        $c = $l . (is_array($data)? implode('', $data) : $data)
            . chr(15 + rand(0, 0xFF));
        for ($i = strlen($c);$i < $targetLength;$i++)
        {
            $m = rand(0x2C, 0xFFF);
            $c .= chr(15 + rand($m, 0xFFF - $m));
        }
        return $c;
    }
    public function unpad($text)
    {
        $len = intval(substr($text, 0, 3));
        return substr($text, 3, $len);
    }
}
?>