<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\AbstractType as FormFieldType;
use Symfony\Component\Form\FormBuilderInterface;

use Millwright\ConfigurationBundle\ORM\Expr;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if ($options['apply_filter'] instanceof \Closure || is_callable($options['apply_filter'])) {
            $builder->setAttribute('apply_filter', $options['apply_filter']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
             'required'     => false,
             'apply_filter' => null,
             'compound'     => false,
        ));
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
        return 'lexik_form_filter.transformer.default';
    }

    /**
     * Default implementation of the applyFieldFilter() method.
     * We just add a 'and where' clause.
     */
    public function applyFilter(QueryBuilder $queryBuilder, Expr $e, $field, array $values)
    {
        if (!empty($values['value'])) {
            $queryBuilder->andWhere($e->eq($field, $values['value']));
        }
    }

}
