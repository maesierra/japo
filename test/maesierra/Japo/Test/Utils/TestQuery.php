<?php
/**
 * Created by PhpStorm.
 * User: maesierra
 * Date: 30/09/2018
 * Time: 9:50
 */

namespace maesierra\Japo\Test\Utils;

use Doctrine\ORM\AbstractQuery;

/**
 * Doctrine's Query is final so it cannot be mocked and AbstractQuery misses a couple  of methods
 */
class TestQuery extends AbstractQuery {

    public function getSQL() {

    }

    protected function _doExecute() {

    }
    public function setMaxResults($maxResults) {

    }

    public function setFirstResult($firstResult) {

    }
}