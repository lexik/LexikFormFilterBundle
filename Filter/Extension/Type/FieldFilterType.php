<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\Extension\Core\Type\FieldType as FormFieldType;
use Symfony\Component\Form\FormBuilder;

use Doctrine\ORM\QueryBuilder;

/**
 * Base filter type.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class FieldFilterType extends FormFieldType implements FilterTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if ($options['apply_filter'] instanceof \Closure || is_callable($options['apply_filter'])) {
            $builder->setAttribute('apply_filter', $options['apply_filter']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(array $options)
    {
        $options = parent::getDefaultOptions($options);
        $options['required'] = false;
        $options['apply_filter'] = null;

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_field';
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerId()
    {
        return 'lexik_filter.transformer.default';
    }

    /**
     * Default implementation of the applyFieldFilter() method.
     * We just add a 'and where' clause.
     */
    public function applyFilter(QueryBuilder $queryBuilder, $field, $values)
    {
        if (!empty($values['value'])) {
            $paramName = sprintf('%s_param', $field);

            $queryBuilder->andWhere(sprintf('%s.%s = :%s', $queryBuilder->getRootAlias(), $field, $paramName))
                ->setParameter($paramName, $values['value'], \PDO::PARAM_STR);
        }
    }
}
