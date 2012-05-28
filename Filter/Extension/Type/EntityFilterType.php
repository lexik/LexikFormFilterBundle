<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Lexik\Bundle\FormFilterBundle\Filter\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter type for related entities.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class EntityFilterType extends EntityType implements FilterTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'filter_choice';
    }

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
    public function getTransformerId()
    {
        return 'lexik_form_filter.transformer.default';
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(QueryBuilder $queryBuilder, Expr $e, $field, array $values)
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
                    $alias = $value['alias'];
                    $joinAlias = 'a' . $alias;
                    $queryBuilder
                        ->leftJoin($field, $joinAlias)
                        ->andWhere($e->in($joinAlias, $ids));
                }

            } else {
                if (!is_callable(array($values['value'], 'getId'))) {
                    throw new \Exception(sprintf('Can\'t call method "getId()" on an instance of "%s"', get_class($values['value'])));
                }

                $queryBuilder->andWhere($e->eq($field, $values['value']->getId()));
            }
        }
    }
}
