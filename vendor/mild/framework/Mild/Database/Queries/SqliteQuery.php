<?php
/**
 * Mild Framework component
 *
 * @author Mochammad Riyadh Ilham Akbar Pasya
 * @link https://github.com/mildphp/mild
 * @copyright 2018
 * @license https://github.com/mildphp/mild/blob/master/LICENSE (MIT Licence)
 */
namespace Mild\Database\Queries;

use Mild\Supports\Collection;

class SqliteQuery extends PostgresQuery
{

    /**
     * @param Query $union
     * @param bool $all
     * @return $this
     */
    public function union($union, $all = false)
    {
        if ($this->unionClause !== null) {
            $this->unionClause .= ' ';
        }
        if ($union->columns === null) {
            $union->columns = ['*'];
        }
        $distinct = ' ';
        $type = 'union ';
        if ($all === true) {
            $type .= 'all ';
        }
        if ($this->distinctClause === true) {
            $distinct .= 'distinct ';
        }
        $this->unionClause .= $type.'select * from (select'.$distinct.$this->wrap($union->columns).' from '.$this->wrap($union->getTable()).$union->resolveClause($union->joinClause).$union->resolveClause($union->whereClause).$union->resolveClause($union->havingClause).$union->resolveClause($union->groupClause).$union->resolveClause($union->orderClause).$union->resolveClause($union->limitClause).$union->resolveClause($union->offsetClause).')';
        return $this->setBinding('union', $union->getBindings());
    }

    /**
     * @param $type
     * @param array $columns
     * @return mixed
     */
    public function aggregate($type, $columns = ['*'])
    {
        if ($this->columns !== null) {
            $columns = $this->columns;
        }
        $table = $this->wrap($this->table);
        $columns = $this->wrap($columns);
        $joinClause = $this->resolveClause($this->joinClause);
        $whereClause = $this->resolveClause($this->whereClause);
        $havingClause = $this->resolveClause($this->havingClause);
        $groupClause = $this->resolveClause($this->groupClause);
        $orderClause = $this->resolveClause($this->orderClause);
        $limitClause = $this->resolveClause($this->limitClause);
        $offsetClause = $this->resolveClause($this->offsetClause);
        if ($this->distinctClause === true) {
            $columns = 'distinct '.$columns;
        }
        $sql = 'select '.$type.'('.$columns.') as aggregate from ';
        if ($this->unionClause !== null) {
            $sql .= '(select * from (select '.$columns.' from '.$table.$joinClause.$whereClause.$havingClause.$groupClause.$orderClause.')'.$this->resolveClause($this->unionClause).$limitClause.$offsetClause.') as '.$this->wrap('temp_table');
        } else {
            $sql .= $table.$joinClause.$whereClause.$havingClause.$groupClause.$orderClause.$limitClause.$offsetClause;
        }
        $this->database->bindValues($stmt = $this->database->prepare($sql), $this->getBindings());
        $results = $this->fetch($stmt);
        if ($results !== []) {
            return $results[0]->aggregate;
        }
        return 0;
    }

    /**
     * @param array $columns
     * @return bool
     */
    public function exists($columns = ['*'])
    {
        $distinct = ' ';
        if ($this->distinctClause === true) {
            $distinct .= 'distinct ';
        }
        if ($this->columns !== null) {
            $columns = $this->columns;
        }
        $sql = 'select'.$distinct.$this->wrap($columns).' from '.$this->wrap($this->table).$this->resolveClause($this->joinClause).$this->resolveClause($this->whereClause).$this->resolveClause($this->havingClause).$this->resolveClause($this->groupClause).$this->resolveClause($this->orderClause).$this->resolveClause($this->limitClause).$this->resolveClause($this->offsetClause);
        if ($this->unionClause !== null) {
            $sql = 'select * from ('.$sql.')'.$this->resolveClause($this->unionClause);
        }
        $sql = 'select exists ('.$sql.') as '.$this->wrap('exists');
        $this->database->bindValues($stmt = $this->database->prepare($sql), $this->getBindings());
        $stmt->execute();
        $results = $this->fetch($stmt);
        if ($results !== []) {
            return (bool) $results[0]->exists;
        }
        return false;
    }

    /**
     * @param array $columns
     * @return Collection
     */
    public function get($columns = ['*'])
    {
        $distinct = ' ';
        if ($this->columns !== null) {
            $columns = $this->columns;
        }
        if ($this->distinctClause === true) {
            $distinct .= 'distinct ';
        }
        $sql = 'select'.$distinct.$this->wrap($columns).' from '.$this->wrap($this->table).$this->resolveClause($this->joinClause).$this->resolveClause($this->whereClause).$this->resolveClause($this->havingClause).$this->resolveClause($this->groupClause).$this->resolveClause($this->orderClause).$this->resolveClause($this->limitClause).$this->resolveClause($this->offsetClause);
        if ($this->unionClause !== null) {
            $sql = 'select * from ('.$sql.')'.$this->resolveClause($this->unionClause);
        }
        $this->database->bindValues($stmt = $this->database->prepare($sql), $this->getBindings());
        $stmt->execute();
        return new Collection($this->fetch($stmt));
    }
}