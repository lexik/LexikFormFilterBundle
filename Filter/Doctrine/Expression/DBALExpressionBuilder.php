<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Doctrine\Expression;

use Doctrine\DBAL\Query\Expression\ExpressionBuilder as Expr;

class DBALExpressionBuilder extends ExpressionBuilder
{
    /**
     * Construct.
     *
     * @param Expr $expr
     */
    public function __construct(Expr $expr)
    {
        $this->expr = $expr;
    }
}
