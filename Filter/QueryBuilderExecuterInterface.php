<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

interface QueryBuilderExecuterInterface
{
    public function addOnce($tag, \Callback $callback);

    /**
     * @return string
     */
    public function getAlias();

    /**
     * @return array
     */
    public function getParts();
}
