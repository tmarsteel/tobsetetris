<?php
namespace cryptmail\crypto;
use cryptmail\crypto\CryptoUtils;
use cryptmail\crypto\RSAPadding;

class RSACipher
{
    protected $e;
    protected $n;
    protected $blockSize = 96;
    protected $padding;
    
    public function __construct($exponent, $modulus, $base = 10)
    {
        if ($base != 10)
        {
            $exponent = CryptoUtils::bcBaseConv($exponent, $base, 10);
            $modulus = CryptoUtils::bcBaseConv($modulus, $base, 10);
        }
        $this->e = $exponent;
        $this->n = $modulus;
        
        // it is assumed that the raw block-size is 128 (1024/8) but
        // because of the base64_encoding the input block-size is reduced
        // to 96 bytes
        // calculation of the actual blocksize:
        
        /*
        $bits = strlen(CryptoUtils::bcBaseConv($modulus, $base, 2));
        $full = $bits / 8;
        // WIKIPEDIA-Formula:
        // base64-encoded string with n bytes has a length of
        // 4 * (n + 2 - ((n + 2) mod 3)) / 3
        // bytes
        $i = floor(3 * $full / 4);
        $this->blockSize = $i + $i % 3;
         */
        $this->padding = new RSAPadding();
    }
    
    // $message string A base36-integer representing the message.
    // returns The result as a base10-integer
    public function rawProcess($message, $inputBase = 36)
    {
        $message = CryptoUtils::bcBaseConv($message, $inputBase, 10);
        return \bcpowmod($message, $this->e, $this->n);
    }
    
    public function decrypt($message, $inBase = 36)
    {
        $blocks = explode(";", $message);
        $result = "";
        foreach ($blocks as $block)
        {
            $result .= $this->singleBlockDecrypt($block, $inBase);
        }
        return $result;
    }
    public function encrypt($message, $outBase = 36)
    {
        $j = strlen($message);
        $bs = $this->blockSize - $this->padding->getMinimumOffset();
        if ($j <= $bs)
        {
            return $this->singleBlockEncrypt($message, 36);
        }
        else
        {
            $ar = array();
            while ($j > 0)
            {
                $ar []= $this->singleBlockEncrypt(substr($message, 0, $bs), $outBase);
                $message = substr($message, $bs + 1);
                $j -= $this->blockSize;
            }
            return implode(";", $ar);
        }
    }
    
    private function singleBlockEncrypt($message, $outBase = 36)
    {
        $message = CryptoUtils::toHexString(
            base64_encode(
                $this->padding->pad($message, $this->blockSize)
            )
        );
        $c = $this->rawProcess($message, 16);
        return CryptoUtils::bcBaseConv($c, 10, $outBase);
    }
    private function singleBlockDecrypt($message, $inBase = 36)
    {
        $raw = $this->rawProcess($message, $inBase);
        $hex = CryptoUtils::bcBaseConv($raw, 10, 16);
        return $this->padding->unpad(base64_decode(CryptoUtils::fromHexString($hex)));
    }
    
    public function getBlockSize()
    {
        return $this->blockSize;
    }
}
?>