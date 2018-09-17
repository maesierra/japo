<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 15/09/18
 * Time: 22:37
 */

namespace maesierra\Japo\Common\Query;


class Sort {

    const SORT_ASC = 'asc';
    const SORT_DESC = 'desc';

    /** @var string */
    public $field;
    /** @var string */
    public $direction;

    /**
     * Sort constructor.
     * @param $field string
     * @param $direction string
     */
    public function __construct($field, $direction = SORT_DESC)
    {
        $this->field = $field;
        $this->direction = $direction;
    }


}