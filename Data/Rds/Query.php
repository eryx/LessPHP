<?php

namespace LessPHP\Data\Rds;

class Query
{
    /**
     * Clause list
     *
     * @var array
     */
    protected $clauses = array();

    /**
     * Generic clause
     *
     * You can use any clause by doing $query->foo('bar')
     * but concrete adapters should be able to recognise it
     *
     * The call will be iterpreted as clause 'foo' with argument 'bar'
     *
     * @param  string $name Clause/method name
     * @param  mixed $args
     * @return LessPHP\Data\Rds\Query
     */
    public function __call($name, $args)
    {
        $this->clauses[] = array(strtolower($name), $args);
        return $this;
    }

    /**
     * SELECT clause (fields to be selected)
     *
     * @param  null|string|array $select
     * @return LessPHP\Data\Rds\Query
     */
    public function select($select = '*')
    {
        if (empty($select)) {
            return $this;
        }
        if (!is_string($select) && !is_array($select)) {
            throw new \Exception("SELECT argument must be a string or an array of strings", 100);
        }
        if (is_string($select)) {
            $select = explode(',', $select);
        }
        $this->clauses['select'] = array('select', $select);
        return $this;
    }

    /**
     * FROM clause
     *
     * @param  string $name Field names
     * @return LessPHP\Data\Rds\Query
     */
    public function from($name)
    {
        //if(!is_string($name)) {
        //    throw new \Exception("FROM argument must be a string", 100);
        //}
        if (preg_match('/^[a-z][a-z0-9_]*$/i', $name) == false) {
            throw new \Exception("FROM argument can contain only alphanumeric characters, _", 100);
        }
        $this->clauses[] = array('from', $name);
        return $this;
    }

    /**
     * WHERE query
     *
     * @param string $cond Condition
     * @param array $args Arguments to substitute instead of ?'s in condition
     * @param string $op relation to other clauses - and/or
     * @return LessPHP\Data\Rds\Query
     */
    public function where($cond, $value = null, $op = 'and')
    {
        if (!is_string($cond)) {
            throw new \Exception("WHERE argument must be a string", 100);
        }
        $this->clauses[] = array('where', array($cond, $value, $op));
        return $this;
    }

    /**
     * Select record or fields by ID
     *
     * @param  string|int $value Identifier to select by
     * @return LessPHP\Data\Rds\Query
     */
    public function whereId($value)
    {
        if (!is_scalar($value)) {
            throw new \Exception("WHEREID argument must be a scalar", 100);
        }
        $this->clauses[] = array('whereid', $value);
        return $this;
    }
    
    /**
     * Sets a limit count and offset to the query.
     *
     * @param int $count OPTIONAL The number of rows to return.
     * @param int $offset OPTIONAL Start returning after this many rows.
     * @return LessPHP\Data\Rds\Query object.
     */
    public function limit($count = 10, $offset = 0)
    {
        //$this->clauses[self::LIMIT_COUNT]    = (int) $count;
        //$this->clauses[self::LIMIT_OFFSET] = (int) $offset;
        if ($offset > 1) {
            $limit = (int)$offset .','. (int)$count;
        } else {
            $limit = (int)$count;
        }
        $this->clauses['limit'] = array('limit', $limit);
        return $this;
    }

    /**
     * ORDER clause; field or fields to sort by, and direction to sort
     *
     * @param  string|int|array $sort
     * @param  string $direction
     * @return LessPHP\Data\Rds\Query
     */
    public function order($sort, $direction = 'asc')
    {
        $this->clauses[] = array('order', array($sort, $direction));
        return $this;
    }
    
    /**
     * Adds grouping to the query.
     *
     * @param  string|int|array $sort
     * @param  string $direction
     * @return LessPHP\Data\Rds\Query
     */
    public function group($sort, $direction = 'asc')
    {
        $this->clauses[] = array('group', array($sort, $direction));
        return $this;
    }

    /**
     * Assemble the query into a format the adapter can utilize
     *
     * @var    string $tableName Name of table from which to select
     * @return string
     */
    public function assemble($tableName = null)
    {
        $select = null;
        $from   = null;
        $where  = null;
        $order  = null;
        $group  = null;
        $limit  = null;
        
        foreach ($this->clauses as $clause) {
        
            list($name, $args) = $clause;

            switch ($name) {
                case 'select':
                    //$select = $args[0];
                    if (null === $select) {
                        $select = implode(',', $args);
                    } else {
                        $select .= ', '. implode(',', $args);
                    }
                    break;
                case 'from':
                    if (null === $from) {
                        // Only allow setting FROM clause once
                        $from = $args;//$adapter->quoteName($args);
                    }
                    break;
                case 'where':
                    $statement = $this->_parseWhere($args[0], $args[1]);
                    if (null === $where) {
                        $where = $statement;
                    } else {
                        $operator = empty($args[2]) ? 'AND' : $args[2];
                        $where .= ' ' . $operator . ' ' . $statement;
                    }
                    break;
                case 'whereid':
                    $statement = $this->_parseWhere('ItemName() = ?', array($args));
                    if (null === $where) {
                        $where = $statement;
                    } else {
                        $operator = empty($args[2]) ? 'AND' : $args[2];
                        $where .= ' ' . $operator . ' ' . $statement;
                    }
                    break;
                case 'order':
                    if (null !== $order) {
                        $order .= ', ';
                    }
                    $order .= $args[0];//$adapter->quoteName($args[0]);
                    if (isset($args[1])) {
                        $order .= ' ' . $args[1];
                    }
                    break;
                case 'group':
                    if (null !== $group) {
                        $group .= ', ';
                    }
                    $group .= $args[0];//$adapter->quoteName($args[0]);
                    if (isset($args[1])) {
                        $group .= ' ' . $args[1];
                    }
                    break;
                case 'limit':
                    $limit = $args;
                    break;
                default:
                    // Ignore unknown clauses
                    break;
            }
        }

        if (empty($select)) {
            $select = "*";
        }
        if (empty($from)) {
            if (null === $tableName) {
                throw new \Exception("Query requires a FROM clause");
            }
            $from = $tableName;//$adapter->quoteName($tableName);
        }
        $query = "SELECT $select FROM $from";
        if (!empty($where)) {
            $query .= " WHERE $where";
        }
        if (!empty($order)) {
            $query .= " ORDER BY $order";
        }
        if (!empty($group)) {
            $query .= " GROUP BY $group";
        }
        if (!empty($limit)) {
            $query .= " LIMIT $limit";
        }
        return $query;
    }
    
    public function reset($keys)
    {
        if (is_string($keys)) {
            $keys = array($keys);
        }

        foreach ($this->clauses as $key => $clause) {
        
            //list($name, $args) = $clause;
            
            if (in_array($clause[0], $keys)) {
                unset($this->clauses[$key]);
            }
        }

        
        return $this;
    }
    
    /**
     * Parse a where statement into service-specific language
     *
     * @todo     Ensure this fulfills the entire SimpleDB query specification for WHERE
     * @param    string $where
     * @param    array $args
     * @return string
     */
    protected function _parseWhere($where, $args)
    {
        if (!is_array($args)) {
            $args = (array) $args;
        }
        //---$adapter = $this->getAdapter()->getClient();
        $i = 0;
        while (false !== ($pos = strpos($where, '?'))) {

            $args[$i] = "'" . str_replace("'", "''", $args[$i]) . "'"; // quote
            //$where = substr_replace($where, $adapter->quote($args[$i]), $pos);

            $where = substr_replace($where, $args[$i], $pos, 1);

            ++$i;
        }
        if (('(' != $where[0]) || (')' != $where[strlen($where) - 1])) {
            $where = '(' . $where . ')';
        }
        return $where;
    }
    
}
