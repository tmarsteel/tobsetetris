<?php
namespace ttetris;

class Game
{
    private $matrix;
    private $points = 0;
    private $comboStreak = 0;
    private $id;
    
    public function __construct()
    {
        $this->matrix = new Matrix(10, 19, Matrix::CELL_NOT_SET);
        $this->id = "" . md5(microtime(true) + rand(200, 2000000));
    }
    
    public function getCurrentScore()
    {
        return $this->points;
    }
    
    /**
     * Returns a unique game id
     */
    public function getID()
    {
        return $this->id;
    }
    
    /**
     * To be called when the brick <code>$brick</code> settles. Recalculates the
     * geme matrics and the score.
     * @param \ttetris\Brick $brick
     */
    public function onBrickPlaced(Brick $brick)
    {
        self::brickCollides($brick, $this->matrix);
        
        // the brick does not collide => set the matrix values
        $brickMatrix = $brick->getStoneMatrix();
        
        for ($c = 0;$c < $brickMatrix->getWidth();$c++)
        {
            for ($r = 0;$r < $brickMatrix->getHeight();$r++)
            {
                if ($brickMatrix->get($r, $c) == Matrix::CELL_SET)
                {
                    $this->matrix->set($brick->getPositionY() + $r, $brick->getPositionX() + $c, Matrix::CELL_SET);
                }
            }
        }
        
        $nRemovedLines = self::removeFullRows($this->matrix);
        
        if ($nRemovedLines == 0)
            $this->comboStreak = 0;
        else
            $this->comboStreak++;
        
        $this->points += self::getAdditionalPoints($nRemovedLines, $this->comboStreak);
        
        
    }
    
    /**
     * Calculates the amount of additional points based on the number of removed
     * lines and the current combo streak.
     * @param int $nRemovedLines
     * @return int
     */
    private static function getAdditionalPoints($nRemovedLines, $comboStreak)
    {        
        if ($nRemovedLines == 0)
        {
            return 1; // 1 point for settling the brick
        }

        $additionalPoints = 0;
        
        if ($nRemovedLines == 1)
        {
            $additionalPoints = 15;
        }
        else if ($nRemovedLines == 2)
        {
            $additionalPoints = 225;
        }
        else if ($nRemovedLines == 3)
        {
            $additionalPoints = 990;
        }
        else if ($nRemovedLines == 4)
        {
            $additionalPoints = 2300;
        }
        else
        {
            $additionalPoints = pow(5, $nRemovedLines);
        }

        $additionalPoints *= $comboStreak;
        
        return $additionalPoints;
    }
    
    /**
     * Removes full rows from the given matrix (shifting up rows with a lower
     * index than removed rows).
     * @param \ttetris\Matrix $matrix
     * @return int The number of rows removed
     */
    public static function removeFullRows(Matrix $matrix)
    {
        $nRemovedRows = 0;
        
        for ($row = 0;$row < $matrix->getHeight();$row++)
        {
            $curRowAll = true;
            $curRowOne = false;
            
            for ($col = 0;$col < $matrix->getWidth();$col++)
            {
                $curRowAll = $curRowAll && ($matrix->get($row, $col) == Matrix::CELL_SET);
                $curRowOne = $curRowOne || ($matrix->get($row, $col) == Matrix::CELL_SET);
            }
            
            if ($curRowAll && $curRowOne)
            {
                // remove row $row
                
                // 1st. shift all rows with a lower index +1 row
                for ($sRow = $row -1 ;$sRow >= 0;$sRow--)
                {
                    for ($sCol = 0;$sCol < $matrix->getWidth();$sCol++)
                    {
                        $matrix->set($sRow + 1, $sCol,
                            $matrix->get($sRow, $sCol));
                    }
                }

                // 2nd unset row #0
                for ($sCol = 0;$sCol < $matrix->getWidth();$sCol++)
                {
                    $matrix->set(0, $sCol, Matrix::CELL_NOT_SET);
                }
                
                $nRemovedRows++;
            }
        }
        
        return $nRemovedRows;
    }
    
    /**
     * Checks whether the given brick collides with stones applied in the given matrix.
     * If it collides, throws a CollisionException; otherwise returns void.
     * @param \ttetris\Brick $brick
     * @param \ttetris\Matrix $GMATRIX
     * @return void
     */
    private static function brickCollides(Brick $brick, Matrix $GMATRIX)
    {
        $brickMatrix = $brick->getStoneMatrix();
        
        // dies the brick exceed the boundaries of $GMATRIX?
        if ($brick->getPositionX() < 0 || $brick->getPositionY() < 0 ||
            $GMATRIX->getHeight() < $brick->getPositionY() + $brickMatrix->getHeight() ||
            $GMATRIX->getWidth() < $brick->getPositionX() + $brickMatrix->getWidth())
        {
            throw new CollisionException("Exceeds matrix bounds.");
        }
        
        // compare the stone matrix
        for ($c = 0;$c < $brickMatrix->getWidth();$c++)
        {
            for ($r = 0;$r < $brickMatrix->getHeight();$r++)
            {
                if ($brickMatrix->get($r, $c) == Matrix::CELL_SET)
                {
                    if ($GMATRIX->get($brick->getPositionY() + $r, $brick->getPositionX() + $c))
                    {
                        throw new CollisionException("Intersects with set stones at "
                            . ($brick->getPositionX() + $c) . ":" . ($brick->getPositionY() + $r));
                    }
                }
            }
        }
    }
    
    public function __toString()
    {
        return $this->matrix->__toString();
    }
}