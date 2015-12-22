<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

/**
 * This class aim to regroup constants used in form filter types and in expression classes.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
final class FilterOperands
{
    const OPERATOR_EQUAL              = 'eq';
    const OPERATOR_GREATER_THAN       = 'gt';
    const OPERATOR_GREATER_THAN_EQUAL = 'gte';
    const OPERATOR_LOWER_THAN         = 'lt';
    const OPERATOR_LOWER_THAN_EQUAL   = 'lte';

    const STRING_STARTS   = 1;
    const STRING_ENDS     = 2;
    const STRING_EQUALS   = 3;
    const STRING_CONTAINS = 4;

    /**
     * @deprecated use FilterOperands::STRING_CONTAINS
     */
    const STRING_BOTH = 4;

    const OPERAND_SELECTOR = 'selection';

    /**
     * Returns all available number operands.
     *
     * @param boolean $includeSelector
     * @return array
     */
    public static function getNumberOperands($includeSelector = false)
    {
        $values = array(
            self::OPERATOR_EQUAL,
            self::OPERATOR_GREATER_THAN,
            self::OPERATOR_GREATER_THAN_EQUAL,
            self::OPERATOR_LOWER_THAN,
            self::OPERATOR_LOWER_THAN_EQUAL,
        );

        if ($includeSelector) {
            $values[] = self::OPERAND_SELECTOR;
        }

        return $values;
    }

    /**
     * Returns all available string operands.
     *
     * @param boolean $includeSelector
     * @return array
     */
    public static function getStringOperands($includeSelector = false)
    {
        $values = array(
            self::STRING_STARTS,
            self::STRING_ENDS,
            self::STRING_EQUALS,
            self::STRING_CONTAINS,
        );

        if ($includeSelector) {
            $values[] = self::OPERAND_SELECTOR;
        }

        return $values;
    }

    /**
     * Retruns an array of available conditions operator for numbers.
     *
     * @return array
     */
    public static function getNumberOperandsChoices()
    {
        $choices = array();

        $reflection = new \ReflectionClass(__CLASS__);
        foreach ($reflection->getConstants() as $name => $value) {
            if ('OPERATOR_' === substr($name, 0, 9)) {
                $choices[$value] = strtolower(str_replace('OPERATOR_', 'number.', $name));
            }
        }

        return array_flip($choices);
    }

    /**
     * Retruns an array of available conditions patterns for string.
     *
     * @return array
     */
    public static function getStringOperandsChoices()
    {
        $choices = array();

        $reflection = new \ReflectionClass(__CLASS__);
        foreach ($reflection->getConstants() as $name => $value) {
            if ('STRING_' === substr($name, 0, 7)) {
                $choices[$value] = strtolower(str_replace('STRING_', 'text.', $name));
            }
        }

        return array_flip($choices);
    }
}
