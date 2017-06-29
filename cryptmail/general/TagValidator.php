<?php
namespace cryptmail\general;

use \cryptmail\sql\QueryCollection;
use \cryptmail\sql\MySqlException;

/**
 * Created on 09.08.2012
 * @author Tobias Marstaller 
 * @desc Used to check tags on whatever item
 */
class TagValidator {

    /**
     * @desc Runs the given tags through the blacklist and returns one tag that is invalid or void
     * @throws MySqlException
     * @param -string $tags 
     * @return string/void
     */
    public static function validateTags(array $tags)
    {
        $query = Query::get('tag_validation');
        
        // All tags will be in the query as follows:
        // WHERE string = TAG OR string = TAG OR string = TAG...
        $query->setParams(0, $tags, ' OR string = ');
        $res = $query->execute();
        
        if ($res->getNumRows() != 0)
        {
            $data = $res->asNumericArray();
            $res->close();
            return $data[0];
        }
    }

}

?>
