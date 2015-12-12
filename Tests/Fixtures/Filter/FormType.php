<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\Query\QueryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class);
        $builder->add('position', IntegerType::class, array(
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                if (!empty($values['value'])) {
                    if ($filterQuery->getExpr() instanceof \Doctrine\MongoDB\Query\Expr) {
                        $expr = $filterQuery->getExpr()->field($field)->equals($values['value']);
                    } else {
                        $expr = $filterQuery->getExpr()->eq($field, $values['value']);
                    }

                    return $filterQuery->createCondition($expr);
                }

                return null;
            },
        ));
    }

    public function getBlockPrefix()
    {
        return 'my_form';
    }
}
