<?php
/**
 * Created by IntelliJ IDEA.
 * User: maesierra
 * Date: 14/09/18
 * Time: 21:10
 */

namespace maesierra\Japo\Common\Query;


class Page implements \JsonSerializable
{

    private $page;
    private $pageSize;
    private $total;

    function __construct($page, $pageSize, $total = null) {
        $this->page = $page;
        $this->pageSize = $pageSize;
        $this->total = $total;
    }

    public function getNPages() {
        if (!$this->total || $this->total === 0) {
            return 0;
        }
        return ceil($this->total / $this->pageSize);
    }

    public function hasMore() {
        return $this->page < ($this->getNPages() - 1);
    }

    public function getOffset() {
        return $this->page * $this->pageSize;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return mixed
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @return mixed
     */
    public function getTotal()
    {
        return $this->total;
    }


    public function jsonSerialize() {
        return [
            "page" => $this->page,
            "pageSize" => $this->pageSize,
            "nPages" => $this->getNPages(),
            "hasMore" => $this->hasMore()
        ];
    }
}