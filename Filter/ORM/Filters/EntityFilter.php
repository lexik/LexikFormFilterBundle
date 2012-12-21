<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\ORM\Filters;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\Collection;

use Lexik\Bundle\FormFilterBundle\Filter\ORM\ORMFilter;
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;

/**
 * Filter type for related entities.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class EntityFilter extends ORMFilter
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_entity';
    }

    /**
     * {@inheritdoc}
     */
    protected function apply(QueryBuilder $filterBuilder, Expr $expr, $field, array $values)
    {
        if (is_object($values['value'])) {
            if ($values['value'] instanceof Collection) {
                $ids = array();

                foreach ($values['value'] as $value) {
                    if (!is_callable(array($value, 'getId'))) {
                        throw new \Exception(sprintf('Can\'t call method "getId()" on an instance of "%s"', get_class($value)));
                    }
                    $ids[] = $value->getId();
                }

                if (count($ids) > 0) {
                    $filterBuilder->andWhere($expr->in($field, $ids));
                }

            } else {
                if (!is_callable(array($values['value'], 'getId'))) {
                    throw new \Exception(sprintf('Can\'t call method "getId()" on an instance of "%s"', get_class($values['value'])));
                }

                $filterBuilder->andWhere($expr->eq($field, $values['value']->getId()));
            }
        }
    }
}
