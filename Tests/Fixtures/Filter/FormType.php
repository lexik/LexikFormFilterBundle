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
class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('position', 'integer', array(
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                if (!empty($values['value'])) {
                    return $filterQuery->createCondition(
                        $filterQuery->getExpr()->eq($field, $values['value'])
                    );
                }

                return null;
            },
        ));
    }

    public function getName()
    {
        return 'my_form';
    }
}
