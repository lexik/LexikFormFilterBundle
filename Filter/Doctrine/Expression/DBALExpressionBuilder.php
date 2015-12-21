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
    public function __construct(Expr $expr, $forceCaseInsensitivity, $encoding = null)
    {
        $this->expr = $expr;
        parent::__construct($forceCaseInsensitivity, $encoding);
    }
}
