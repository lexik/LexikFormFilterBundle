<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

/**
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class RelationsAliasBag
{
    /**
     * @var array
     */
    private $aliases;

    /**
     * @param array $aliases
     */
    public function __construct(array $aliases = array())
    {
        $this->aliases = $aliases;
    }

    /**
     * @param string $relation
     * @return string
     */
    public function get($relation)
    {
        return $this->aliases[$relation];
    }

    /**
     * @param string $relation
     * @param string $alias
     */
    public function add($relation, $alias)
    {
        $this->aliases[$relation] = $alias;
    }

    /**
     * @param string $relation
     * @return bool
     */
    public function has($relation)
    {
        return isset($this->aliases[$relation]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->aliases);
    }
}
