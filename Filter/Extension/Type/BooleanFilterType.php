<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\AbstractType;

use Doctrine\ORM\QueryBuilder;

/**
 * Filter to use with boolean values.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class BooleanFilterType extends AbstractType implements FilterTypeInterface
{
    const VALUE_YES = 'y';
    const VALUE_NO  = 'n';

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'filter_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_boolean';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'choices' => array(
                self::VALUE_YES  => 'boolean.yes',
                self::VALUE_NO   => 'boolean.no'
            ),
            'empty_value' => 'boolean.yes_or_no',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(QueryBuilder $queryBuilder, $field, $values)
    {
        if (!empty($values['value'])) {
            $paramName = sprintf('%s_param', $field);

            $queryBuilder->andWhere(sprintf('%s.%s = :%s', $queryBuilder->getRootAlias(), $field, $paramName))
                ->setParameter($paramName, (int) (self::VALUE_YES == $values['value']), \PDO::PARAM_BOOL);
        }
    }
}