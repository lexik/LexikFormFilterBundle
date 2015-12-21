<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Doctrine\Expression;

use Doctrine\ORM\Query\Expr;

class ORMExpressionBuilder extends ExpressionBuilder
{
    /**
     * Construct.
     *
     * @param Expr $expr
     */
    public function __construct(Expr $expr, $forceCaseInsensitivity, $encoding = null)
    {
        $this->expr = $expr;
        parent::__construct($forceCaseInsensitivity, $encoding);
    }
}
