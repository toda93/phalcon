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
        if (preg_match('/WHERE/i', $this->builder->getPhql())) {
            return $this->andWhere($column, $operator, $value);
        } else {
            if (is_null($operator)) {
                $this->builder->where($column);
            } else {
                if (is_null($value)) {
                    $value = $operator;
                    $operator = '=';
                }
                $this->builder->where("$column $operator :B{$this->countBind}:", array('B' . $this->countBind++ => $value));
            }
            return $this;
        }

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

        if (is_null($operator)) {
            $this->builder->andWhere($column);
        } else {
            if (is_null($value)) {
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
        if (is_null($operator)) {
            $this->builder->orWhere($column);
        } else {
            if (is_null($value)) {
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

    public function innerJoin($model, $conditions = null, $alias = null)
    {
        if (!is_null($conditions)) {
            $this->builder->innerJoin($model, $conditions);
        } else if (!is_null($alias)) {
            $this->builder->innerJoin($model, $conditions, $alias);
        } else {
            $this->builder->innerJoin($model);
        }
        return $this;
    }

    public function leftJoin($model, $conditions = null, $alias = null)
    {
        if (!is_null($conditions)) {
            $this->builder->leftJoin($model, $conditions);
        } else if (!is_null($alias)) {
            $this->builder->leftJoin($model, $conditions, $alias);
        } else {
            $this->builder->leftJoin($model);
        }
        return $this;
    }

    public function rightJoin($model, $conditions = null, $alias = null)
    {
        if (!is_null($conditions)) {
            $this->builder->rightJoin($model, $conditions);
        } else if (!is_null($alias)) {
            $this->builder->rightJoin($model, $conditions, $alias);
        } else {
            $this->builder->rightJoin($model);
        }
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


    public function limit($take, $skip = 0)
    {
        return $this->take($take)->skip($skip);
    }

    public function getQuery()
    {
        return $this->builder->getPhql();
    }

    public function get()
    {
        return $this->builder->getQuery()->execute();
    }

    public function getArray($key, $value)
    {
        $data = $this->builder->getQuery()->execute();
        $result = [];
        foreach ($data as $item) {
            $result[$item->$key] = $item->$value;
        }
        return $result;
    }

    public function first()
    {
        return $this->builder->limit(1)->getQuery()->getSingleResult();
    }

    public function count()
    {
        $result = $this->builder->columns('COUNT(*) as total')->getQuery()->getSingleResult();
        return $result->total;
    }

    public function pagination($page = 1, $limit = 50, $total = 99999999)
    {
        $obj = new \stdClass();
        $obj->total_items = $total;
        $obj->last = $obj->total_pages = (int)($total / $limit);
        $obj->current = $page;
        $obj->before = ($page > 1) ? $page - 1 : 1;
        $obj->next = ($page < $obj->total_pages) ? $page + 1 : $obj->total_pages;
        $obj->items = $this->limit($limit, (($page - 1) * $limit))->builder->getQuery()->execute();

        return $obj;
    }

    public function paginationRaw($query, $page = 1, $limit = 50, $total = 99999999)
    {
        $query = preg_replace('/LIMIT(.*)/i', '', $query);
        $count_query = preg_replace('/select(.*?)from(.*?)/i', 'SELECT COUNT(*) as total FROM', $query);

        $query .= " LIMIT $limit OFFSET " . (($page - 1) * $limit);

        $obj = new \stdClass();
        $obj->total_items = $total;
        $obj->last = $obj->total_pages = (int)($total / $limit);
        $obj->current = $page;
        $obj->before = ($page > 1) ? $page - 1 : 1;
        $obj->next = ($page < $obj->total_pages) ? $page + 1 : $obj->total_pages;
        $obj->items = $this->selectRaw($query);

        return $obj;
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

    public function selectRaw($query, $first = false)
    {
        $class = $this->builder->getFrom();
        $obj = new $class;
        $result = new Resultset(null, $obj, $obj->getReadConnection()->query($query));
        if ($first) {
            return $result->getFirst();
        }
        return $result;
    }

    public function updateRaw($query)
    {
        $class = $this->builder->getFrom();
        $obj = new $class;
        return $obj->getWriteConnection()->query($query);
    }


}