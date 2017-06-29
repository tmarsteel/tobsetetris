<?php
namespace ttetris;

abstract class Brick
{
    const ROTATION_0DEG = 0;
    const ROTATION_90DEG = 90;
    const ROTATION_180DEG = 180;
    const ROTATION_270DEG = 270;
    
    const TYPE_I = "i";
    
    const TYPE_L = "l";
    
    const TYPE_L_REV = "rl";
    
    const TYPE_T = "t";
    
    const TYPE_Z = "z";
    
    const TYPE_S = "s";
    
    const TYPE_SQUARE = "square";
    
    public static function getInstance($type)
    {
        switch ($type)
        {
            case self::TYPE_I:
                return new IBrick();
            case self::TYPE_L:
                return new LBrick();
            case self::TYPE_L_REV:
                return new RevLBrick();
            case self::TYPE_T:
                return new TBrick();
            case self::TYPE_Z:
                return new ZBrick();
            case self::TYPE_S:
                return new SBrick();
            case self::TYPE_SQUARE:
                return new SquareBrick();
            default:
                throw new \InvalidArgumentException("Unknown brick type.");
        }
    }
    
    /**
     * Returns an array of matrices as per brickdef.
     * @param array $brickdef
     */
    protected static function getRotationMatrices($brickdef)
    {
        $matrices = array();
        
        foreach ($brickdef as $rotation => $matrixDef)
        {
            $sizes = $matrixDef[0];
            $matrixDef = $matrixDef[1];
            
            $matrices[$rotation] = new Matrix($sizes[0], $sizes[1], Matrix::CELL_NOT_SET);
            
            for ($m0 = 0;$m0 < $sizes[1];$m0++)
            {
                for ($m1 = 0;$m1 < $sizes[0];$m1++)
                {
                    $matrices[$rotation]->set($m0, $m1, $matrixDef[$m0][$m1]);
                }
            }
        }
        
        return $matrices;
    }
    
    private $x = 0;
    private $y = 0;
    private $rotation = self::ROTATION_0DEG;
    private $stoneMatrix;
    
    private $rotatedMatrices = array();
    
    protected function __construct(Matrix $matrix0deg, Matrix $matrix90deg,
        Matrix $matrix180deg, Matrix $matrix270deg)
    {
        $this->rotatedMatrices[self::ROTATION_0DEG] = $matrix0deg;
        $this->rotatedMatrices[self::ROTATION_90DEG] = $matrix90deg;
        $this->rotatedMatrices[self::ROTATION_180DEG] = $matrix180deg;
        $this->rotatedMatrices[self::ROTATION_270DEG] = $matrix270deg;
        $this->stoneMatrix = $this->rotatedMatrices[self::ROTATION_0DEG];
    }
    
    /**
     * Returns a string representing the type of this brick.
     * @return brick
     */
    public abstract function getTypeAsString();
    
    /**
     * Returns the stone matrix representing this brick in its current rotation.
     * @return Matrix
     */
    public function getStoneMatrix()
    {
        return $this->rotatedMatrices[$this->rotation];
    }
    
    /**
     * Returns the current rotation of this brick.
     * @see ROTATION_0DEG
     * @see ROTATION_90DEG
     * @see ROTATION_180DEG
     * @see ROTATION_270DEG
     */
    public function getRotation()
    {
        return $this->rotation;
    }
    
    /**
     * Sets the current rotation of this brick.
     * @see ROTATION_0DEG
     * @see ROTATION_90DEG
     * @see ROTATION_180DEG
     * @see ROTATION_270DEG
     */
    public function setRotation($rotation)
    {
        if ($rotation == self::ROTATION_0DEG || $rotation == self::ROTATION_90DEG
         || $rotation == self::ROTATION_180DEG || $rotation == self::ROTATION_270DEG)
        {
            $this->rotation = $rotation;
        }
        else
        {
            throw new \InvalidArgumentException("The given rotation must be one of the rotation-constants of Brick");
        }
    }
    
    public function getPositionX()
    {
        return $this->x;
    }
    
    public function setPositionX($x)
    {
        $this->x = $x;
    }
    
    public function getPositionY()
    {
        return $this->y;
    }
    
    public function setPositionY($y)
    {
        $this->y = $y;
    }
}
