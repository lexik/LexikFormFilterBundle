<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

class FilterBuilderExecuter implements FilterBuilderExecuterInterface
{
    /**
     * @var object
     */
    protected $filterBuilder;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @var object
     */
    protected $expr;

    /**
     * @var array
     */
    protected $parts;

    /**
     * Construct.
     *
     * @param object $filterBuilder
     * @param string $alias
     * @param object $expr
     * @param array $parts
     */
    public function __construct($filterBuilder, $alias, $expr, array & $parts = array())
    {
        $this->filterBuilder = $filterBuilder;
        $this->alias        = $alias;
        $this->expr         = $expr;
        $this->parts        = & $parts;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * {@inheritdoc}
     */
    public function getParts()
    {
        return $this->parts;
    }

    /**
     * {@inheritdoc}
     */
    public function addOnce($join, $alias, \Closure $callback)
    {
        if (isset($this->parts[$join])) {
            return null;
        }

        $this->parts[$join] = $alias;

        return $callback($this->filterBuilder, $this->alias, $alias, $this->expr);
    }
}
