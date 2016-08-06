<?php
namespace Toda\Core;

use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class QueryBuilder extends \Phalcon\Mvc\Model\Query\Builder
{
    private $countBind = 0;

    public function __construct($params = null)
    {
        parent::__construct($params);
    }

    public function select($select)
    {
        return $this->columns($select);
    }

    public function where($column, $operator = null, $value = null)
    {
        if (empty($value)) {
            $value = $operator;
            $operator = '=';
        }
        return parent::where("$column $operator :B{$this->countBind}:", array('B' . $this->countBind++ => $value));
    }

    public function whereNull($column)
    {
        return parent::where("$column is null");
    }

    public function whereNotNull($column)
    {
        return parent::where("$column is not null");
    }

    public function whereEmpty($column)
    {
        return parent::where("$column is null or $column = ''");
    }

    public function whereNotEmpty($column)
    {
        return parent::where("$column is not null and $column != ''");
    }

    public function whereIn($column, array $array)
    {

        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        return parent::where("$column IN (" . rtrim($temp, ',') . ")", $array_bind);

    }

    public function whereNotIn($column, array $array)
    {
        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        return parent::where("$column not in (" . rtrim($temp, ',') . ")", $array_bind);
    }

    public function whereBetween($column, $start, $end)
    {
        $temp = "BETWEEN :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $start;
        $temp .= " AND :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $end;
        return parent::where("$column $temp", $array_bind);
    }

    public function andWhere($column, $operator = null, $value = null)
    {
        if (empty($value)) {
            $value = $operator;
            $operator = '=';
        }
        return parent::andWhere("$column $operator :B$this->countBind:", array('B' . $this->countBind++ => $value));
    }

    public function andWhereNull($column)
    {
        return parent::andWhere("$column is null");
    }

    public function andWhereNotNull($column)
    {
        return parent::andWhere("$column is not null");
    }

    public function andWhereEmpty($column)
    {
        return parent::andWhere("$column is null or $column = ''");
    }

    public function andWhereNotEmpty($column)
    {
        return parent::andWhere("$column is not null and $column != ''");
    }

    public function andWhereIn($column, array $array)
    {

        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        return parent::andWhere("$column IN (" . rtrim($temp, ',') . ")", $array_bind);

    }

    public function andWhereNotIn($column, array $array)
    {
        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        return parent::andWhere("$column not in (" . rtrim($temp, ',') . ")", $array_bind);
    }

    public function andWhereBetween($column, $start, $end)
    {
        $temp = "between :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $start;
        $temp .= " and :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $end;
        return parent::andWhere("$column $temp", $array_bind);
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if (empty($value)) {
            $value = $operator;
            $operator = '=';
        }
        return parent::orWhere("$column $operator :B$this->countBind:", array('B' . $this->countBind++ => $value));
    }

    public function orWhereNull($column)
    {
        return parent::orWhere("$column is null");
    }

    public function orWhereNotNull($column)
    {
        return parent::orWhere("$column is not null");
    }

    public function orWhereEmpty($column)
    {
        return parent::orWhere("$column is null or $column = ''");
    }

    public function orWhereNotEmpty($column)
    {
        return parent::orWhere("$column is not null and $column != ''");
    }

    public function orWhereIn($column, array $array)
    {

        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        return parent::orWhere("$column IN (" . rtrim($temp, ',') . ")", $array_bind);

    }

    public function orWhereNotIn($column, array $array)
    {
        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        return parent::orWhere("$column not in (" . rtrim($temp, ',') . ")", $array_bind);
    }

    public function orWhereBetween($column, $start, $end)
    {
        $temp = "between :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $start;
        $temp .= " and :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $end;
        return parent::orWhere("$column $temp", $array_bind);
    }

    public function groupBy($group)
    {

        if(empty($group)){
            return $this;
        }
        return parent::groupBy($group);
    }

    public function orderBy($order)
    {
        if(empty($order)){
            return $this;
        }
        if (is_array($order)) {
            $order = implode(',', $order);
        }

        return parent::orderBy($order);
    }

    public function skip($start)
    {
        return $this->offset($start);
    }

    public function take($num)
    {
        return $this->limit($num);
    }

    public function get()
    {
        return $this->getQuery()->execute();
    }

    public function getOrFail()
    {
        $result = $this->get();
        if (empty($result)) {
            abort(404);
        }
        return $result;
    }

    public function first()
    {
        return $this->limit(1)->getQuery()->getSingleResult();
    }

    public function firstOrNew()
    {
        $result = $this->first();
        if (empty($result)) {
            $class = $this->getFrom();
            $result = new $class;
        }
        return $result;
    }

    public function firstOrFail()
    {
        $result = $this->first();
        if (empty($result)) {
            abort(404);
        }
        return $result;
    }

    public function toArray()
    {
        return $this->getQuery()->execute()->toArray();
    }

    public function toJson()
    {
        return json_encode($this->getQuery()->execute()->toArray());
    }

    public function rawSelect($sql)
    {
        $class = $this->getFrom();
        $obj = new $class;
        return new Resultset(null, $obj, $obj->getReadConnection()->query($sql));
    }
    public function rawUpdate($sql)
    {
        $class = $this->getFrom();
        $obj = new $class;
        return $obj->getWriteConnection()->query($sql);
    }

    public function pagination($page = 1, $limit = 20)
    {
        $result = new \Phalcon\Paginator\Adapter\QueryBuilder(
            array(
                "builder" => $this,
                "limit" => $limit,
                "page" => empty($page) ? 1 : $page,
            )
        );
        return $result->getPaginate();
    }
}