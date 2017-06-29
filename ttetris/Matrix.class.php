<?php
namespace ttetris;

/**
 * Represents a game state matrix.
 * @author Tobias Marstaller
 */
class Matrix
{
    const CELL_NOT_SET = 0;
    const CELL_SET = 1;
    
    protected $matrix;
    protected $width;
    protected $height;
    
    /**
     * Constructs a new matrix.
     * @param int $width Width of the matrix, > 0
     * @param type $height Height of the matrix, > 0
     * @param boolean $defaultValue The initial value of all fields.
     */
    public function __construct($width, $height = null, $defaultValue = self::CELL_NOT_SET)
    {
        if ($height == null)
        {
            $height = $width;
        }
        
        if ($width <= 0 || $height <= 0)
        {
            throw new \InvalidArgumentException("Width and height of a matrix must be greater than 0");
        }
        
        $this->width = $width;
        $this->height = $height;
        
        $this->matrix = array();
        
        for ($row = 0;$row < $height;$row++)
        {
            $this->matrix [$row] = array();
            for ($col = 0;$col < $width;$col++)
            {
                $this->matrix[$row][$col] = $defaultValue;
            }
        }
    }
    
    /**
     * Returns whether this matrix is square.
     * @return boolean
     */
    public function isSquare()
    {
        return $this->width == $this->height;
    }
    
    /**
     * Returns the width of this matrix.
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Returns the height of this matrix.
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Returns the value of the cell at the given row- and column-index.
     * @param int $rowIndex
     * @param int $colIndex
     * @return int
     * @throws OutOfBoundsException
     */
    public function get($rowIndex, $colIndex)
    {
        if ($rowIndex < 0 || $rowIndex >= $this->height
         || $colIndex < 0 || $colIndex >= $this->width)
        {
            throw new \OutOfBoundsException;
        }
        
        return $this->matrix[$rowIndex][$colIndex];
    }
    
    /**
     * Sets the value of the cell at the given row- and column-index.
     * @param int $rowIndex
     * @param int $colIndex
     * @param int $value
     * @throws OutOfBoundsException
     * @throws IllegalArgumentException If $value is no integer.
     */
    public function set($rowIndex, $colIndex, $value)
    {
        if ($rowIndex < 0 || $rowIndex >= $this->height
         || $colIndex < 0 || $colIndex >= $this->width)
        {
            throw new \OutOfBoundsException;
        }
        
        if (!is_int($value))
        {
            throw new \InvalidArgumentException("Only accepting integer values");
        }
        
        return $this->matrix[$rowIndex][$colIndex] = $value;
    }
    
    public function equals(Matrix $m)
    {
        if (!$m->width != $this->width || $m->height != $this->height)
        {
            return false;
        }
        
        for ($row = 0;$row < $this->height;$row++)
        {
            for ($col = 0;$col < $this->width;$col++)
            {
                if ($m->matrix[$row][$col] != $this->matrix[$row][$col])
                {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Returns an array representation of this matrix.
     * @return int[][]
     */
    public function toRaw()
    {
        return $this->matrix;
    }
    
    public function __toString()
    {
        $out = "+" . str_repeat("-", $this->width) . "+\n";
        for ($row = 0;$row < $this->height;$row++)
        {
            $out .= "|";
            
            for ($col = 0;$col < $this->width;$col++)
            {
                $out .= $this->get($row, $col) == self::CELL_SET? "X" : " ";
            }
            
            $out .= "|\n";
        }
        
        return $out . "+" . str_repeat("-", $this->width) . "+";
    }
}