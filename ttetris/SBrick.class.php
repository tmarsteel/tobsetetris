<?php
namespace ttetris;

class SBrick extends Brick
{
    private static $BRICKDEF = array(
        self::ROTATION_0DEG => array(
            array(3, 2),
            array(
                array(Matrix::CELL_NOT_SET, Matrix::CELL_SET, Matrix::CELL_SET),
                array(Matrix::CELL_SET,     Matrix::CELL_SET, Matrix::CELL_NOT_SET)
            )
        ),
        self::ROTATION_90DEG => array(
            array(2, 3),
            array(
                array(Matrix::CELL_SET,     Matrix::CELL_NOT_SET),
                array(Matrix::CELL_SET,     Matrix::CELL_SET),
                array(Matrix::CELL_NOT_SET, Matrix::CELL_SET)
            )
        ),
        self::ROTATION_180DEG => array(
            array(3, 2),
            array(
                array(Matrix::CELL_NOT_SET, Matrix::CELL_SET, Matrix::CELL_SET),
                array(Matrix::CELL_SET,     Matrix::CELL_SET, Matrix::CELL_NOT_SET)
            )
        ),
        self::ROTATION_270DEG => array(
            array(2, 3),
            array(
                array(Matrix::CELL_SET,     Matrix::CELL_NOT_SET),
                array(Matrix::CELL_SET,     Matrix::CELL_SET),
                array(Matrix::CELL_NOT_SET, Matrix::CELL_SET)
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
        return Brick::TYPE_S;
    }
}
