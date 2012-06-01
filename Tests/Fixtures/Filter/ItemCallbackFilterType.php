<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ItemCallbackFilterType extends AbstractType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('name', 'filter_text', array(
            'apply_filter' => array($this, 'fieldNameCallback'),
        ));
        $builder->add('position', 'filter_number', array(
            'apply_filter' => function($queryBuilder, $expr, $field, $values) {
                if (!empty($values['value'])) {
                    $queryBuilder->andWhere($expr->neq($field, $values['value']));
                }
            },
        ));
    }

    public function getName()
    {
        return 'item_filter';
    }

    public function fieldNameCallback($queryBuilder, $expr, $field, $values)
    {
        if (!empty($values['value'])) {
            $value = sprintf($values['condition_pattern'], $values['value']);
            $queryBuilder->andWhere($expr->neq($field, $value));
        }
    }
}