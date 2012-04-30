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
            'apply_filter' => function($queryBuilder, $alias, $field, $values) {
                if (!empty($values['value'])) {
                    $paramName = sprintf('%s_param', $field);
                    $condition = sprintf('%s.%s <> :%s',
                        $alias,
                        $field,
                        $paramName
                    );

                    $queryBuilder->andWhere($condition)
                        ->setParameter($paramName, $values['value']);
                }
            },
        ));
    }

    public function getName()
    {
        return 'item_filter';
    }

    public function fieldNameCallback($queryBuilder, $alias, $field, $values)
    {
        if (!empty($values['value'])) {
            $paramName = sprintf('%s_param', $field);
            $value = sprintf($values['condition_pattern'], $values['value']);
            $condition = sprintf('%s.%s <> :%s',
                $alias,
                $field,
                $paramName
            );

            $queryBuilder->andWhere($condition)
                ->setParameter($paramName, $value, \PDO::PARAM_STR);
        }
    }
}