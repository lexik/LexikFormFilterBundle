<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Form\FormBuilder;

use Symfony\Component\Form\AbstractType;

class DateRangeFilterType extends AbstractType implements FilterTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('left_date', 'filter_date', $options['left_date']);
        $builder->add('right_date', 'filter_date', $options['right_date']);

        $builder->setAttribute('filter_value_keys', array(
                'left_date' => $options['left_date'],
                'right_date' => $options['right_date']));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'left_date' => array(),
            'right_date' => array(),
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
        return 'filter_date_range';
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerId()
    {
        return 'lexik_form_filter.transformer.value_keys';
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(QueryBuilder $queryBuilder, $field, $values)
    {
        if ($values['value']['left_date'][0] instanceof \DateTime) {
            $leftParamName = sprintf('left_%s_param', $field);
            $condition = sprintf('%s.%s %s :%s',
                $queryBuilder->getRootAlias(),
                $field,
                '>=',
                $leftParamName
            );

            $queryBuilder->andWhere($condition)
                ->setParameter($leftParamName, $values['value']['left_date'][0]->format('Y-m-d'), \PDO::PARAM_STR);
        }

        if ($values['value']['right_date'][0] instanceof \DateTime) {
            $rightParamName = sprintf('right_%s_param', $field);
            $condition = sprintf('%s.%s %s :%s',
                $queryBuilder->getRootAlias(),
                $field,
                '<=',
                $rightParamName
            );

            $queryBuilder->andWhere($condition)
                ->setParameter($rightParamName, $values['value']['right_date'][0]->format('Y-m-d'), \PDO::PARAM_STR);
        }
    }
}
