<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Doctrine\ORM\QueryBuilder;

/**
 * Filter type for boolean.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class CheckboxFilterType extends CheckboxType implements FilterTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return 'filter_field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_checkbox';
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
        if (!empty($values['value'])) {
            $paramName = sprintf('%s_param', $field);

            $queryBuilder->andWhere(sprintf('%s.%s = :%s', $queryBuilder->getRootAlias(), $field, $paramName))
                ->setParameter($paramName, $values['value'], \PDO::PARAM_BOOL);
        }
    }
}