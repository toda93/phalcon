<?php
namespace Toda\Core;

use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class QueryBuilder
{
    private $countBind = 0;

    private $builder = null;

    public function __construct($params = null)
    {
        $this->builder = new Builder($params);
    }

    public function select($select)
    {
        $this->builder->columns($select);
        return $this;
    }

    public function whereRaw($query)
    {
        $this->builder->where($query);
        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        if (empty($operator)) {
            $this->builder->where($column);
        } else {
            if (empty($value)) {
                $value = $operator;
                $operator = '=';
            }
            $this->builder->where("$column $operator :B{$this->countBind}:", array('B' . $this->countBind++ => $value));
        }
        return $this;
    }

    public function whereNull($column)
    {
        $this->builder->where("$column is null");
        return $this;
    }

    public function whereNotNull($column)
    {
        $this->builder->where("$column is not null");
        return $this;
    }

    public function whereEmpty($column)
    {
        $this->builder->where("$column is null or $column = ''");
        return $this;
    }

    public function whereNotEmpty($column)
    {
        $this->builder->where("$column is not null and $column != ''");
        return $this;
    }

    public function whereIn($column, array $array)
    {

        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        $this->builder->where("$column IN (" . rtrim($temp, ',') . ")", $array_bind);
        return $this;

    }

    public function whereNotIn($column, array $array)
    {
        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        $this->builder->where("$column not in (" . rtrim($temp, ',') . ")", $array_bind);
        return $this;
    }

    public function whereBetween($column, $start, $end)
    {
        $temp = "BETWEEN :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $start;
        $temp .= " AND :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $end;
        $this->builder->where("$column $temp", $array_bind);
        return $this;
    }

    public function andWhere($column, $operator = null, $value = null)
    {

        if (empty($operator)) {
            $this->builder->where($column);
        } else {
            if (empty($value)) {
                $value = $operator;
                $operator = '=';
            }
            $this->builder->andWhere("$column $operator :B{$this->countBind}:", array('B' . $this->countBind++ => $value));
        }
        return $this;
    }

    public function andWhereNull($column)
    {
        $this->builder->andWhere("$column is null");
        return $this;
    }

    public function andWhereNotNull($column)
    {
        $this->builder->andWhere("$column is not null");
        return $this;
    }

    public function andWhereEmpty($column)
    {
        $this->builder->andWhere("$column is null or $column = ''");
        return $this;
    }

    public function andWhereNotEmpty($column)
    {
        $this->builder->andWhere("$column is not null and $column != ''");
        return $this;
    }

    public function andWhereIn($column, array $array)
    {

        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        $this->builder->andWhere("$column IN (" . rtrim($temp, ',') . ")", $array_bind);
        return $this;

    }

    public function andWhereNotIn($column, array $array)
    {
        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        $this->builder->andWhere("$column not in (" . rtrim($temp, ',') . ")", $array_bind);
        return $this;
    }

    public function andWhereBetween($column, $start, $end)
    {
        $temp = "between :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $start;
        $temp .= " and :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $end;
        $this->builder->andWhere("$column $temp", $array_bind);
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if (empty($operator)) {
            $this->builder->orWhere($column);
        } else {
            if (empty($value)) {
                $value = $operator;
                $operator = '=';
            }
            $this->builder->orWhere("$column $operator :B{$this->countBind}:", array('B' . $this->countBind++ => $value));
        }
        return $this;
    }

    public function orWhereNull($column)
    {
        $this->builder->orWhere("$column is null");
        return $this;
    }

    public function orWhereNotNull($column)
    {
        $this->builder->orWhere("$column is not null");
        return $this;
    }

    public function orWhereEmpty($column)
    {
        $this->builder->orWhere("$column is null or $column = ''");
        return $this;
    }

    public function orWhereNotEmpty($column)
    {
        $this->builder->orWhere("$column is not null and $column != ''");
        return $this;
    }

    public function orWhereIn($column, array $array)
    {

        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        $this->builder->orWhere("$column IN (" . rtrim($temp, ',') . ")", $array_bind);
        return $this;

    }

    public function orWhereNotIn($column, array $array)
    {
        $array_bind = [];
        $temp = '';
        foreach ($array as $item) {
            $temp .= ":B{$this->countBind}:,";
            $array_bind['B' . $this->countBind++] = $item;
        }

        $this->builder->orWhere("$column not in (" . rtrim($temp, ',') . ")", $array_bind);
        return $this;
    }

    public function orWhereBetween($column, $start, $end)
    {
        $temp = "between :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $start;
        $temp .= " and :B$this->countBind:";
        $array_bind['B' . $this->countBind++] = $end;
        $this->builder->orWhere("$column $temp", $array_bind);
        return $this;
    }

    public function groupBy($group)
    {

        if (empty($group)) {
            return $this;
        }
        $this->builder->groupBy($group);
        return $this;
    }

    public function orderBy($order)
    {
        if (empty($order)) {
            return $this;
        }
        if (is_array($order)) {
            $order = implode(',', $order);
        }

        $this->builder->orderBy($order);
        return $this;
    }

    public function skip($start)
    {
        $this->builder->offset($start);
        return $this;
    }

    public function take($num)
    {
        $this->builder->limit($num);
        return $this;
    }


    public function limit($take, $skip)
    {
        return $this->take($take)->skip($skip);
    }

    public function getQuery(){
        return $this->builder->getPhql();
    }

    public function get()
    {
        return $this->builder->getQuery()->execute();
    }

    public function first()
    {
        return $this->builder->limit(1)->getQuery()->getSingleResult();
    }

    public function firstOrNew()
    {
        $result = $this->first();
        if (empty($result)) {
            $class = $this->builder->getFrom();
            $result = new $class;
        }
        return $result;
    }

    public function toArray()
    {
        return $this->builder->getQuery()->execute()->toArray();
    }

    public function toJson()
    {
        return json_encode($this->builder->getQuery()->execute()->toArray());
    }

    public function rawSelect($sql)
    {
        $class = $this->builder->getFrom();
        $obj = new $class;
        return new Resultset(null, $obj, $obj->getReadConnection()->query($sql));
    }

    public function rawUpdate($sql)
    {
        $class = $this->builder->getFrom();
        $obj = new $class;
        return $obj->getWriteConnection()->query($sql);
    }

    public function pagination($page = 1, $limit = 50)
    {
        $result = new \Phalcon\Paginator\Adapter\QueryBuilder(
            array(
                "builder" => $this->builder,
                "limit" => $limit,
                "page" => empty($page) ? 1 : $page,
            )
        );
        return $result->getPaginate();
    }
}