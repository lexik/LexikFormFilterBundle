<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Doctrine\ORM\Query\Expr;
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
                                                         'left_date'  => $options['left_date'],
                                                         'right_date' => $options['right_date']
                                                    ));
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'left_date'  => array(),
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
    public function applyFilter(QueryBuilder $queryBuilder, Expr $e, $field, $values)
    {
        $value      = $values['value'];
        $leftValue  = $value['left_date'][0];
        $rightValue = $value['right_date'][0];

        if ($leftValue instanceof \DateTime) {
            $leftDate = $leftValue->format('Y-m-d');
            $queryBuilder->andWhere($e->gte($field, $leftDate));
        }

        if ($rightValue instanceof \DateTime) {
            $rightDate = $rightValue->format('Y-m-d');
            $queryBuilder->andWhere($e->lte($field, $rightDate));
        }
    }
}
