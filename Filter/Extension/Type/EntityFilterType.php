<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Doctrine\ORM\QueryBuilder;

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
    public function getParent(array $options)
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
    public function applyFilter(QueryBuilder $queryBuilder, $field, $values)
    {
        if (is_object($values['value'])) {
            if (!is_callable(array($values['value'], 'getId'))) {
                throw new \Exception(sprintf('Can\'t call method "getId()" on an instance of "%s"', get_class($values['value'])));
            }

            $paramName = sprintf(':%s_param', $field);

            $queryBuilder->andWhere(sprintf('%s.%s = %s', $queryBuilder->getRootAlias(), $field, $paramName))
                ->setParameter($paramName, $values['value']->getId(), \PDO::PARAM_INT);
        }
    }
}