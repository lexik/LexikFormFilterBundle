<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Doctrine\ODM\MongoDB\Query\Expr;
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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextFilterType::class, ['apply_filter' => [$this, 'fieldNameCallback']]);
        $builder->add('position', NumberFilterType::class, ['apply_filter' => function (QueryInterface $filterQuery, $field, $values) {
            if (!empty($values['value'])) {
                if ($filterQuery->getExpr() instanceof Expr) {
                    $expr = $filterQuery->getExpr()->field($field)->notEqual($values['value']);
                } else {
                    $expr = $filterQuery->getExpr()->neq($field, $values['value']);
                }

                return $filterQuery->createCondition($expr);
            }

            return null;
        }]);
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
            } else {
                $expr = $filterQuery->getExpr()->neq($field, sprintf('\'%s\'', $values['value']));
            }

            return $filterQuery->createCondition($expr);
        }

        return null;
    }
}
