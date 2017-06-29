<?php
namespace ttetris;

class IBrick extends Brick
{
    private static $BRICKDEF = array(
        self::ROTATION_0DEG => array(
            array(1, 4),
            array(
                array(Matrix::CELL_SET),
                array(Matrix::CELL_SET),
                array(Matrix::CELL_SET),
                array(Matrix::CELL_SET)
            )
        ),
        self::ROTATION_90DEG => array(
            array(4, 1),
            array(
                array(Matrix::CELL_SET, Matrix::CELL_SET, Matrix::CELL_SET, Matrix::CELL_SET)
            )
        ),
        self::ROTATION_180DEG => array(
            array(1, 4),
            array(
                array(Matrix::CELL_SET),
                array(Matrix::CELL_SET),
                array(Matrix::CELL_SET),
                array(Matrix::CELL_SET)
            )
        ),
        self::ROTATION_270DEG => array(
            array(4, 1),
            array(
                array(Matrix::CELL_SET, Matrix::CELL_SET, Matrix::CELL_SET, Matrix::CELL_SET)
            )
        )
    );
    
    public function __construct()
    {
        $matrices = self::getRotationMatrices(self::$BRICKDEF);
        
        parent::__construct(
            $matrices[self::ROTATION_0DEG],
            $matrices[self::ROTATION_90DEG],
            $matrices[self::ROTATION_180DEG],
            $matrices[self::ROTATION_270DEG]
        );
    }
    
    public function getTypeAsString() {
        return Brick::TYPE_I;
    }
}
