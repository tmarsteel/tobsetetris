<?php
namespace ttetris;

/**
 * A deterministic, saltable random number generator. Based on a very simple, custom hash function.
 * The custom function was crafted to make JS implementation easy (as opposed to SHA-256 or similar).
 * The hash function is based on the Rijhandel S-Box and modulo.
 * @author Tobias Marstaller <tobias.marstaller@gmail.com>
 */
class DPRNG
{
    /**
     * The rijhandel s-box
     */
    private static $diffusionTable = array(
        0x63, 0x7c, 0x77, 0x7b, 0xf2, 0x6b, 0x6f, 0xc5, 0x30, 0x01, 0x67, 0x2b, 0xfe, 0xd7, 0xab, 0x76,
        0xca, 0x82, 0xc9, 0x7d, 0xfa, 0x59, 0x47, 0xf0, 0xad, 0xd4, 0xa2, 0xaf, 0x9c, 0xa4, 0x72, 0xc0,
        0xb7, 0xfd, 0x93, 0x26, 0x36, 0x3f, 0xf7, 0xcc, 0x34, 0xa5, 0xe5, 0xf1, 0x71, 0xd8, 0x31, 0x15,
        0x04, 0xc7, 0x23, 0xc3, 0x18, 0x96, 0x05, 0x9a, 0x07, 0x12, 0x80, 0xe2, 0xeb, 0x27, 0xb2, 0x75,
        0x09, 0x83, 0x2c, 0x1a, 0x1b, 0x6e, 0x5a, 0xa0, 0x52, 0x3b, 0xd6, 0xb3, 0x29, 0xe3, 0x2f, 0x84,
        0x53, 0xd1, 0x00, 0xed, 0x20, 0xfc, 0xb1, 0x5b, 0x6a, 0xcb, 0xbe, 0x39, 0x4a, 0x4c, 0x58, 0xcf,
        0xd0, 0xef, 0xaa, 0xfb, 0x43, 0x4d, 0x33, 0x85, 0x45, 0xf9, 0x02, 0x7f, 0x50, 0x3c, 0x9f, 0xa8,
        0x51, 0xa3, 0x40, 0x8f, 0x92, 0x9d, 0x38, 0xf5, 0xbc, 0xb6, 0xda, 0x21, 0x10, 0xff, 0xf3, 0xd2,
        0xcd, 0x0c, 0x13, 0xec, 0x5f, 0x97, 0x44, 0x17, 0xc4, 0xa7, 0x7e, 0x3d, 0x64, 0x5d, 0x19, 0x73,
        0x60, 0x81, 0x4f, 0xdc, 0x22, 0x2a, 0x90, 0x88, 0x46, 0xee, 0xb8, 0x14, 0xde, 0x5e, 0x0b, 0xdb,
        0xe0, 0x32, 0x3a, 0x0a, 0x49, 0x06, 0x24, 0x5c, 0xc2, 0xd3, 0xac, 0x62, 0x91, 0x95, 0xe4, 0x79,
        0xe7, 0xc8, 0x37, 0x6d, 0x8d, 0xd5, 0x4e, 0xa9, 0x6c, 0x56, 0xf4, 0xea, 0x65, 0x7a, 0xae, 0x08,
        0xba, 0x78, 0x25, 0x2e, 0x1c, 0xa6, 0xb4, 0xc6, 0xe8, 0xdd, 0x74, 0x1f, 0x4b, 0xbd, 0x8b, 0x8a,
        0x70, 0x3e, 0xb5, 0x66, 0x48, 0x03, 0xf6, 0x0e, 0x61, 0x35, 0x57, 0xb9, 0x86, 0xc1, 0x1d, 0x9e,
        0xe1, 0xf8, 0x98, 0x11, 0x69, 0xd9, 0x8e, 0x94, 0x9b, 0x1e, 0x87, 0xe9, 0xce, 0x55, 0x28, 0xdf,
        0x8c, 0xa1, 0x89, 0x0d, 0xbf, 0xe6, 0x42, 0x68, 0x41, 0x99, 0x2d, 0x0f, 0xb0, 0x54, 0xbb, 0x16
    );

    /**
     * The initial state; simply stored for getInitialSalt
     * @var int
     */
    private $initialState = null;

    /**
     * The current state; the salt is the initial state
     * @var int
     */
    private $state = 0;

    /**
     * The counter. Is XORed with the state prior to each generation step.
     * @var int
     */
    private $counter = 0;

    /**
     * @param int $salt The 28bit salt / initial state to use. If omitted, one is
     * obtained through mcrypt_create_iv if available, rand() otherwise.
     */
    public function __construct($salt = null)
    {
        if ($salt === null)
        {
            if (function_exists("mcrypt_create_iv"))
            {
                $rand = mcrypt_create_iv(4);
                $salt = (ord($rand[0]) << 20) | (ord($rand[1]) << 12) | (ord($rand[2]) << 4) | (ord($rand[3]) & 0xF);
            }
            else if (function_exists("random_int")) {
                $salt = random_int(0, 0xFFFFFFF);
            }
            else
            {
                $salt = rand(0, 0xFFFFFFF);
            }
        }

        $this->initialState = $salt & 0xFFFFFFF;
        $this->state = $this->initialState;
    }

	/**
	 * Returns a pseudo-random, uniformly distributed <code>double</code> value in
	 * the range 0 inclusive to 1 exclusive.
	 * @return double
	 */
	public function next() {
		return ((float) $this->nextInt(0, 0xFFFFFFF)) / ((float) 0xFFFFFFF);
	}

	/**
	 * Returns a pseudo-random uniformly distributed <code>double</code> value in
	 * the range <code>$min</code> inclusive to <code>$max</code> inclusive.
	 * @param double $min
	 * @param double $max
	 * @return double
	 */
	public function nextDouble($min, $max)
	{
		if ($max < $min)
		{
			return $this->nextDouble($max, $min);
		}

		return $min + $this->next() * ($max - $min);
	}

    /**
     * Returns a pseudo-random uniformly distributed <code>int<code> value in
	 * the range <code>$min</code> inclusive to <code>$max</code> <b>inclusive</b>.
     * @param int $min
     * @param int $max
	 * @return int
     */
    public function nextInt($min, $max)
    {
        if ($max < $min)
        {
            return $this->nextInt($max, $min);
        }
        else if ($max == $min)
        {
            return $min;
        }

        $rangeSize = $max - $min;

        $nRequiredBits = min(ceil(log($rangeSize, 2)), 32);
        $result = null;

        if ($nRequiredBits > 28)
        {
            $additionalBits = 32 - $nRequiredBits;
            $mask = pow(2, $additionalBits) - 1;

            $result = (($this->advance() << $additionalBits) | ($this->advance() & $mask));
        }
        else
        {
            $mask = pow(2, $nRequiredBits) - 1;
            $result = ($this->advance() & $mask);
        }

        // for ranges that are not a 2-complement, larger values than $max may
        // be in $result => reduce to the range by dividing by 2
        while ($min + $result > $max) $result = floor($result / 2);

        return $result;
    }

	/**
	 * Returns an array of length <code>$n</code> with each element being
	 * a pseudo-random and uniformly distributed integer in the range 0 to 255 inclusive.
	 * @param int $n The number of bytes to generate
	 * @return int[]
	 */
	public function nextBytes($n) {
		$ar = array();
		for ($i = 0;$i < $n;$i++)
		{
			$ar []= $this->nextInt(0, 255);
		}

		return $ar;
	}

    /**
     * Advances the inner state and returns random 28 bits.
	 * @return int
     */
    private function advance()
    {
        $hash = self::hash($this->state ^ $this->counter);
        
        $this->state ^= self::hash($this->state);
        
        $this->counter++;
        if ($this->counter > 0xFFFFFFF)
        {
            $this->counter = 0;
        }
        
        return $hash;
    }
    
    /**
     * Returns the salt his RNG was initialized with.
     * @return int
     */
    public function getInitialSalt()
    {
        return $this->initialState;
    }
    
    /**
     * The underlying hash function (int28 -> int28)
     * @param int $input
     */
    private static function hash($input)
    {              
        // 5 rounds of each: diffusion through substitution, recombine,
        // diffusion through multiplaction and modulo
        for ($i = 0;$i < 5;$i++)
        {	
            // console.log("round " + i);
            // extract the rightmost 4 bytes
            $byte4 =  $input & 0x0F;
            $byte3 = ($input >> 4) & 0xFF;
            $byte2 = ($input >> 12) & 0xFF;
            $byte1 = ($input >> 20) & 0xFF;

            // substitute and recombine
            $input = (self::$diffusionTable[$byte1] << 20) |
                     (self::$diffusionTable[$byte2] << 12) |
                     (self::$diffusionTable[$byte3] << 4 ) |
                     $byte4;

            // multiplicate and reduce to 24 bit range
            $input = abs($input * 0x7) % 0xFFFFFFF;
        }

        return $input;
    }
}
