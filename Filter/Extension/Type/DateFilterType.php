<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilder;

use Doctrine\ORM\QueryBuilder;

class DateFilterType extends DateType implements FilterTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return $options['widget'] === 'single_text' ? 'filter_field' : 'filter';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_date';
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerId()
    {
        return 'lexik_form_filter.transformer.default';
    }

    /**
    * {@inheritdoc}
    */
    public function applyFilter(QueryBuilder $queryBuilder, $field, $values)
    {
        if ($values['value'] instanceof \DateTime) {
            $paramName = sprintf('%s_param', $field);
            $condition = sprintf('%s.%s = :%s',
                $queryBuilder->getRootAlias(),
                $field,
                $paramName
            );

            $queryBuilder->andWhere($condition)
                ->setParameter($paramName, $values['value']->format('Y-m-d'), \PDO::PARAM_STR);
        }
    }
}