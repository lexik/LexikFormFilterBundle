<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Doctrine\ODM\MongoDB\Query\Expr;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\Expression\ExpressionParameterValue;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
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
        $builder->add('name', TextFilterType::class, array(
            'apply_filter' => array($this, 'fieldNameCallback'),
        ));
        $builder->add('position', NumberFilterType::class, array(
            'apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
                if (!empty($values['value'])) {
                    if ($filterQuery->getExpr() instanceof Expr) {
                        $expr = $filterQuery->getExpr()->field($field)->notEqual($values['value']);
                        return $filterQuery->createCondition($expr);
                    }
                    return $filterQuery->createCondition(
                        $filterQuery->getExpr()->neq($field, ':position'),
                        ['position' => new ExpressionParameterValue($values['value'])]
                    );
                }

                return null;
            },
        ));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'item_filter';
    }

    public function fieldNameCallback(QueryInterface $filterQuery, $field, $values)
    {
        if (!empty($values['value'])) {
            if ($filterQuery->getExpr() instanceof Expr) {
                $expr = $filterQuery->getExpr()->field($field)->notEqual($values['value']);
                return $filterQuery->createCondition($expr);
            }
            $paramName = substr($field, strrpos($field, '.') + (false === strrpos($field, '.') ? 0 : 1));
            return $filterQuery->createCondition(
                $filterQuery->getExpr()->neq($field, ':' . $paramName),
                [$paramName => new ExpressionParameterValue($values['value'])]
            );
        }

        return null;
    }
}
