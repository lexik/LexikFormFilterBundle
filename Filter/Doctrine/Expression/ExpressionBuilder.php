<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Doctrine\Expression;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

abstract class ExpressionBuilder
{
    const SQL_DATE      = 'Y-m-d';
    const SQL_DATE_TIME = 'Y-m-d H:i:s';

    /**
     * @var mixed
     */
    protected $expr;

    /**
     * @var boolean
     */
    protected $forceCaseInsensitivity;

    /**
     * @var string
     */
    protected $encoding;

    /**
     * Get expression object.
     */
    public function expr()
    {
        return $this->expr;
    }

    /**
     * @param boolean $forceCaseInsensitivity
     * @param string|null $encoding
     */
    public function __construct($forceCaseInsensitivity, $encoding = null)
    {
        $this->forceCaseInsensitivity = $forceCaseInsensitivity;
        $this->encoding = $encoding;
    }

    /**
     * Returns between expression if min and max not null
     * Returns lte expression if max is null
     * Returns gte expression if min is null
     *
     * @param string $field field name
     * @param number $min minimum value
     * @param number $max maximum value
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison|string
     */
    public function inRange($field, $min, $max)
    {
        if (!$min && !$max) {
            return;
        }

        if (null === $min) {
            // $max exists
            return $this->expr()->lte($field, (float) $max);
        } elseif (null === $max) {
            // $min exists
            return $this->expr()->gte($field, (float) $min);
        }

        // both $min and $max exists
        return $this->between($field, (float) $min, (float) $max);
    }

    /**
     * Creates BETWEEN() function with the given argument.
     *
     * @param string $field field name
     * @param number $min minimum value
     * @param number $max maximum value
     *
     * @return string
     */
    public function between($field, $min, $max)
    {
        return $field . ' BETWEEN ' . $min . ' AND ' . $max;
    }

    /**
     * Returns between expression if min and max not null
     * Returns lte expression if max is null
     * Returns gte expression if min is null
     *
     * @param string        $field field name
     * @param null|DateTime $min   start date
     * @param null|DateTime $max   end date
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison|string
     */
    public function dateInRange($field, $min = null, $max = null)
    {
        if (!$min && !$max) {
            return;
        }

        $min = $this->convertToSqlDate($min);
        $max = $this->convertToSqlDate($max, true);

        if (null === $min) {
            // $max exists
            return $this->expr()->lte($field, $max);
        } elseif (null === $max) {
            // $min exists
            return $this->expr()->gte($field,  $min);
        }

        // both $min and $max exists
        return $this->expr()->andX(
            $this->expr()->lte($field, $max),
            $this->expr()->gte($field,  $min)
        );
    }

    /**
     * Returns between expression if min and max not null
     * Returns lte expression if max is null
     * Returns gte expression if min is null
     *
     * @param  string|DateTime $value alias.fieldName or mysql date string format or DateTime
     * @param  string|DateTime $min alias.fieldName or mysql date string format or DateTime
     * @param  string|DateTime $max alias.fieldName or mysql date string format or DateTime
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
            $findExpression = $this->expr()->lte($value, $max);
        } elseif ($max === null) {
            $findExpression = $this->expr()->gte($value,  $min);
        } else {
            $findExpression = $this->expr()->andX(
                $this->expr()->lte($value, $max),
                $this->expr()->gte($value,  $min)
            );
        }

        return $findExpression;
    }

    /**
     * Get string like expression
     *
     * @param  string $field field name
     * @param  string $value string value
     * @param  int    $type one of FilterOperands::STRING_* constant
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison|string
     */
    public function stringLike($field, $value, $type = FilterOperands::STRING_CONTAINS)
    {
        $value = $this->convertTypeToMask($value, $type);

        return $this->expr()->like(
            $this->forceCaseInsensitivity ? $this->expr()->lower($field) : $field,
            $this->expr()->literal($value)
        );
    }

    /**
     * Normalize DateTime boundary
     *
     * @param  DateTime $date
     * @param  bool     $isMax
     *
     * @return \Doctrine\ORM\Query\Expr\Literal|string
     */
    protected function convertToSqlDate($date, $isMax = false)
    {
        if (! $date instanceof \DateTime) {
            return;
        }

        if ($isMax) {
            $date->modify('+1 day -1 second');
        }

        return $this->expr()->literal($date->format(self::SQL_DATE_TIME));
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
            $date = $this->expr()->literal($date->format(self::SQL_DATE_TIME));
        }

        return $date;
    }

    /**
     * Prepare value for like operation
     *
     * @param string $value
     * @param int    $type one of FilterOperands::STRING_*
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function convertTypeToMask($value, $type)
    {
        if ($this->forceCaseInsensitivity) {
            $value = $this->encoding ? mb_strtolower($value, $this->encoding) : mb_strtolower($value);
        }

        switch ($type) {
            case FilterOperands::STRING_STARTS:
                $value .= '%';
                break;

            case FilterOperands::STRING_ENDS:
                $value = '%' . $value;
                break;

            case FilterOperands::STRING_CONTAINS:
                $value = '%' . $value . '%';
                break;

            case FilterOperands::STRING_EQUALS:
                break;

            default:
                throw new \InvalidArgumentException('Wrong type constant in string like expression mapper');
        }

        return $value;
    }
}
