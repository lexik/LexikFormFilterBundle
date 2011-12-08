<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Doctrine\ORM\QueryBuilder;

/**
 * Filter type for numbers.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class NumberRangeFilterType extends AbstractType implements FilterTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('left_number', 'filter_number', $options['left_number']);
        $builder->add('right_number', 'filter_number', $options['right_number']);

        $builder->setAttribute('filter_value_keys', array('left_number', 'right_number'));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'left_number' => array(),
            'right_number' => array(),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'filter';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_number_range';
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(QueryBuilder $queryBuilder, $field, $values)
    {
        if (isset($values['value']['left_number'], $values['value']['right_number'])) {
            $leftParamName = sprintf('left_%s_param', $field);
            $rightParamName = sprintf('right_%s_param', $field);

            $condition = sprintf('(%s.%s %s :%s AND %s.%s %s :%s)',
                $queryBuilder->getRootAlias(),
                $field,
                $values['left_number']['condition_operator'],
                $leftParamName,
                $queryBuilder->getRootAlias(),
                $field,
                $values['right_number']['condition_operator'],
                $rightParamName
            );

            $queryBuilder->andWhere($condition)
                ->setParameter($leftParamName, $values['value']['left_number'], \PDO::PARAM_INT)
                ->setParameter($rightParamName, $values['value']['right_number'], \PDO::PARAM_INT);
        }
    }
}