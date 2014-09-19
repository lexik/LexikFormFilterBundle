<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ItemCallbackFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'filter_text', array(
            'apply_filter' => array($this, 'fieldNameCallback'),
        ));
        $builder->add('position', 'filter_number', array(
            'apply_filter' => function(QueryInterface $filterQuery, $field, $values) {
                if (!empty($values['value'])) {
                    return $filterQuery->createCondition(
                        $filterQuery->getExpr()->neq($field, $values['value'])
                    );
                }

                return null;
            },
        ));
    }

    public function getName()
    {
        return 'item_filter';
    }

    public function fieldNameCallback(QueryInterface $filterQuery, $field, $values)
    {
        if (!empty($values['value'])) {
            return $filterQuery->createCondition(
                $filterQuery->getExpr()->neq($field, sprintf('\'%s\'', $values['value']))
            );
        }

        return null;
    }
}
