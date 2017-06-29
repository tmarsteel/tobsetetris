<?php
namespace ttetris;

/**
 * Generates a pseudo-random stream of brick types for a tetris game, based on
 * an {@link DPRNG}. It is assured that within 3 consecutive brick types the same
 * type does not appear more than twice.
 */
class BrickSequenceGenerator
{
    protected static $types = array(
        Brick::TYPE_I, Brick::TYPE_L, Brick::TYPE_L_REV, Brick::TYPE_S,
        Brick::TYPE_SQUARE, Brick::TYPE_T, Brick::TYPE_Z
    );
    
    /**
     * The underly random number generator
     * @var DPRNG
     */
    protected $rng;
    
    /**
     * The previous brick type
     * @var string
     */
    protected $previous;
    
    /**
     * The pre-previous brick type
     * @var string
     */
    protected $prePrevious;
    
    public function __construct(DPRNG $rng = null)
    {
        if ($rng == null)
        {
            $rng = new DPRNG();
        }
        
        $this->rng = $rng;
    }
    
    /**
     * Returns the next brick-type in sequence
     * @return string
     */
    public function nextType()
    {
        $type = null;
        
        if ($this->previous == null)
        {
            // first pease. Never spawn S, Z or Square first
            do
            {
                $type = $this->randomBrickType();
            }
            while (in_array($type, array(Brick::TYPE_S, Brick::TYPE_Z, Brick::TYPE_SQUARE)));
        }
        else
        {
            do
            {
                $type = $this->randomBrickType();
            }
            while ($this->previous == $type || $this->prePrevious == $type);

            $this->prePrevious = $this->previous;
        }
        
        $this->previous = $type;
        
        return $type;
    }
    
    /**
     * Returns a random brick-type as per self::$types and $this->rng
     * @return string
     */
    protected function randomBrickType()
    {
        return self::$types[
            $this->rng->nextInt(0, count(self::$types) - 1)
        ];
    }
}