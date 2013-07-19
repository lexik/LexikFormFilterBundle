<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\ORM;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Orx;

/**
 * @deprecated Deprecated since version 2.0, to be removed in 2.1. Use ExpressionBuilder class on Doctrine namespace.
 */
class Expr extends \Doctrine\ORM\Query\Expr
{
    const SQL_DATE      = 'Y-m-d';
    const SQL_DATE_TIME = 'Y-m-d H:i:s';

    /**
     * Returns between expression if min and max not null
     * Returns lte expression if max is null
     * Returns gte expression if min is null
     *
     * @param  string $field field name
     * @param  int $min minimum value
     * @param  int $max maximum value
     * @return \Doctrine\ORM\Query\Expr\Comparison|string
     */
    public function inRange($field, $min, $max)
    {
        if (!$min && !$max) {
            return null;
        }
        if ($min === null) {
            // $max exists
            $findExpression = $this->lte($field, (float) $max);
        } else if ($max === null) {
            // $min exists
            $findExpression = $this->gte($field, (float) $min);
        } else {
            //both $min and $max exists
            $findExpression = $this->between($field,
                (integer) $min,
                (integer) $max
            );
        }

        return $findExpression;
    }

    /**
     * Returns between expression if min and max not null
     * Returns lte expression if max is null
     * Returns gte expression if min is null
     *
     * @param  string|DateTime $minDate alias.fieldName or mysql date string format or  DateTime
     * @param  string|DateTime $maxDate alias.fieldName or mysql date string format or  DateTime
     * @param  string|DateTime $minField alias.fieldName or mysql date string format or  DateTime
     * @param  string|DateTime $maxField alias.fieldName or mysql date string format or  DateTime
     * @return \Doctrine\ORM\Query\Expr\Comparison|string|null
     */
    public function rangeInRange($minDate = null, $maxDate = null, $minField = null, $maxField = null)
    {
        if ( ! $minField && ! $maxField) {
            return null;
        }

        if ( ! $minDate && ! $maxDate) {
            return null;
        }

        $minDate  = $this->convertToSqlDate($minDate);
        $maxDate  = $this->convertToSqlDate($maxDate, true);
        $minField = $this->convertToSqlDate($minField);
        $maxField = $this->convertToSqlDate($maxField, true);

        if ( ! $minField && ! $maxField) {
            return null;
        }

        if (null === $minField || null === $minDate) {
            // $maxField exists and $minDate exists
            $findExpression = $this->gte($maxDate, $maxField);
        } else if (null === $maxField || null === $maxDate) {
            // $minField exists and $minDate exists
            $findExpression = $this->lte($minDate,  $minField);
        } else {
            $findExpression = $this->andX(
                $this->gte($maxDate, $maxField),
                $this->lte($minDate, $minField)
            );
        }

        return $findExpression;
    }

    /**
     * Returns between expression if min and max not null
     * Returns lte expression if max is null
     * Returns gte expression if min is null
     *
     * @param  string|DateTime $value alias.fieldName or mysql date string format or  DateTime
     * @param  string|DateTime $min alias.fieldName or mysql date string format or  DateTime
     * @param  string|DateTime $max alias.fieldName or mysql date string format or  DateTime
     * @return \Doctrine\ORM\Query\Expr\Comparison|string
     */
    public function dateInRange($value, $min = null, $max = null)
    {
        if (!$min && !$max) {
            return null;
        }

        $value = $this->convertToSqlDate($value);
        $min   = $this->convertToSqlDate($min);
        $max   = $this->convertToSqlDate($max, true);

        if (!$max && !$min) {
            return null;
        }

        if ($min === null) {
            // $max exists
            $findExpression = $this->lte($value, $max);
        } else if ($max === null) {
            // $min exists
            $findExpression = $this->gte($value,  $min);
        } else {
            $findExpression = $this->andX(
                $this->lte($value, $max),
                $this->gte($value,  $min)
            );
        }

        return $findExpression;
    }

    /**
     * Returns between expression if min and max not null
     * Returns lte expression if max is null
     * Returns gte expression if min is null
     *
     * @param  string|DateTime $value alias.fieldName or mysql date string format or  DateTime
     * @param  string|DateTime $min alias.fieldName or mysql date string format or  DateTime
     * @param  string|DateTime $max alias.fieldName or mysql date string format or  DateTime
     * @return \Doctrine\ORM\Query\Expr\Comparison|string
     */
    public function dateTimeInRange($value, $min = null, $max = null)
    {
        if (!$min && !$max) {
            return null;
        }

        $value = $this->convertToSqlDateTime($value);
        $min   = $this->convertToSqlDateTime($min);
        $max   = $this->convertToSqlDateTime($max);

        if (!$max && !$min) {
            return null;
        }

        if ($min === null) {
            $findExpression = $this->lte($value, $max);
        } else if ($max === null) {
            $findExpression = $this->gte($value,  $min);
        } else {
            $findExpression = $this->andX(
                $this->lte($value, $max),
                $this->gte($value,  $min)
            );
        }

        return $findExpression;
    }


    /**
     * Prepare value for like operation
     *
     * @param string $value
     * @param int $type one of FilterOperands::STRING_*
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function _convertTypeToMask($value, $type)
    {
        switch($type) {
            case FilterOperands::STRING_STARTS:
                $value .= '%';
                break;

            case FilterOperands::STRING_ENDS:
                $value = '%' . $value;
                break;

            case FilterOperands::STRING_BOTH:
                $value = '%' . $value . '%';
                break;

            case FilterOperands::STRING_EQUALS:
                //return $e->eq($field, $e->literal($value));
                break;

            default:
                throw new \InvalidArgumentException('Wrong type constant in string like expression mapper');
        }

        return $value;
    }

    /**
     * Get string like expression
     *
     * @param  string $field field name
     * @param  string $value string value
     * @param  int    $type one of FilterOperands::STRING_* constant
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function stringLike($field, $value, $type = FilterOperands::STRING_BOTH)
    {
        $value = $this->_convertTypeToMask($value, $type);

        return $this->like($field, $this->literal($value));
    }

    /**
     * Get like expression with string start matching rule
     *
     * @see Expr::stringLike()
     * @param string $field
     * @param string $value
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function stringStarts($field, $value)
    {
        return $this->stringLike($field, $value, FilterOperands::STRING_STARTS);
    }

    /**
     * Get like expression with string end matching rule
     *
     * @see Expr::stringLike()
     * @param string $field
     * @param string $value
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function stringEnds($field, $value)
    {
        return $this->stringLike($field, $value, FilterOperands::STRING_ENDS);
    }

    /**
     * Get like expression with both string and end string matching rule
     *
     * @see Expr::stringLike()
     * @param string $field
     * @param string $value
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function stringBoth($field, $value)
    {
        return $this->stringLike($field, $value, FilterOperands::STRING_BOTH);
    }

    /**
     * Get like expression with equal string matching rule
     *
     * @see Expr::stringLike()
     * @param string $field
     * @param string $value
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function stringEq($field, $value)
    {
        return $this->stringLike($field, $value, FilterOperands::STRING_EQUALS);
    }

    /**
     * Get like expressions for any matching word, separated by space or array elements
     *
     * @param  string $field
     * @param  array|string $values
     * @param  int $type one of self::STRING_*
     * @return Orx
     */
    public function stringLikeAnyWord($field, $values, $type = FilterOperands::STRING_BOTH)
    {
        if (!is_array($values)) {
            $values = explode(' ', $values);
        }

        $exprs = array();

        foreach ($values as $value) {
            $exprs[] = $this->stringLike($field, $value, $type);
        }

        return new Orx($exprs);
    }

    /**
    * Get like expression for any matching word with string end matching rule
    *
    * @see   Expr::stringLikeAnyWord()
    * @param string $field
    * @param string $value
    * @return Orx
    */
    public function stringEndsAnyWord($field, $value)
    {
        return $this->stringLikeAnyWord($field, $value, FilterOperands::STRING_ENDS);
    }

    /**
     * Get like expression  for any matching word with both string and end string matching rule
     *
     * @see   Expr::stringLikeAnyWord()
     * @param string $field
     * @param string $value
     * @return Orx
     */
    public function stringBothAnyWord($field, $value)
    {
        return $this->stringLikeAnyWord($field, $value, FilterOperands::STRING_BOTH);
    }

    /**
     * Get like expression  for any matching word with equal string matching rule
     *
     * @see   Expr::stringLikeAnyWord()
     * @param string $field
     * @param string $value
     * @return Orx
     */
    public function stringEqAnyWord($field, $value)
    {
        return $this->stringLikeAnyWord($field, $value, FilterOperands::STRING_EQUALS);
    }

    /**
     * Creates an instance of Expr\Comparison, with the given arguments.
     * Processing if right expression is NULL
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> <= <right expr>. Example:
     *
     *     [php]
     *     // u.id <= ?1
     *     $q->where($q->expr()->lte('u.id', '?1'));
     *
     * @param    mixed $x Left expression
     * @param    mixed $y Right expression
     * @return   \Doctrine\ORM\Query\Expr\Comparison
     */
    public function lteNull($x, $y)
    {
        if (is_null($y)) {
            return null;
        } else {
            return new \Doctrine\ORM\Query\Expr\Comparison($x, \Doctrine\ORM\Query\Expr\Comparison::LTE, $y);
        }
    }

    /**
     * Creates an instance of Expr\Comparison, with the given arguments.
     * Processing if right expression is NULL
     * First argument is considered the left expression and the second is the right expression.
     * When converted to string, it will generated a <left expr> >= <right expr>. Example:
     *
     *     [php]
     *     // u.id >= ?1
     *     $q->where($q->expr()->gte('u.id', '?1'));
     *
     * @param    mixed $x Left expression
     * @param    mixed $y Right expression
     * @return   \Doctrine\ORM\Query\Expr\Comparison
     */
    public function gteNull($x, $y)
    {
        if (is_null($y)) {
            return null;
        } else {
            return new \Doctrine\ORM\Query\Expr\Comparison($x, \Doctrine\ORM\Query\Expr\Comparison::GTE, $y);
        }
    }

    /**
     * Normalize date boundary
     *
     * @param  DateTime|string $date
     * @param  bool $isMax
     * @return \Doctrine\ORM\Query\Expr\Literal
     */
    protected function convertToSqlDate($date, $isMax = false)
    {
        if ($date instanceof \DateTime) {
            if ($isMax) {
                $date->setTime(23, 59, 59);
            } else {
                $date->setTime(0, 0, 0);
            }

            $date = $this->literal($date->format(self::SQL_DATE));
        }
        return $date;
    }

    /**
     * Normalize date time boundary
     *
     * @param DateTime|string $date
     * @return \Doctrine\ORM\Query\Expr\Literal
     */
    protected function convertToSqlDateTime($date)
    {
        if ($date instanceof \DateTime) {
            $date = $this->literal($date->format(self::SQL_DATE_TIME));
        }

        return $date;
    }

    /**
     * Is value is null or in array or equal to given value
     *
     * @param  string $field
     * @param  string|int|null|array $value
     * @param  boolean $literal if $value is array - add slashes to each element
     *
     * @return string DQL expression
     */
    public function inEq($field, $value, $literal = false)
    {
        if($value === null) {
            $result = $this->isNull($field);
        } else if(is_array($value)) {
            if ($literal) {
                $value = $this->literalize($value);
            }
            $result = $this->in($field, $value);
        } else {
            if(is_string($value)) {
                $value = $this->literal($value);
            }
            $result = $this->eq($field, $value);
        }

        return $result;
    }

    /**
     * Is value is not null or not in array or not equal to given value
     *
     * @param  string $field
     * @param  string|int|null|array $value
     * @param  boolean $literal if $value is array - add slashes to each element
     *
     * @return string DQL expression
     */
    public function inNotEq($field, $value, $literal = false)
    {
        if($value === null) {
            $result = $this->isNotNull($field);
        } else if(is_array($value)) {
            if ($literal) {
                $value = $this->literalize($value);
            }
            $result = $this->notIn($field, $value);
        } else {
            if(is_string($value)) {
                $value = $this->literal($value);
            }
            $result = $this->neq($field, $value);
        }

        return $result;
    }

    /**
     * Add slashes to each array elemets
     *
     * @param  array $value
     * @return array
     */
    private function literalize(array $value)
    {
        foreach($value as &$v) {
            $v  = $this->literal($v);
        }

        return $value;
    }
}
