<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Lexik\Bundle\FormFilterBundle\Filter\Expr;

/**
 * Filter type for related entities.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class EntityFilterType extends AbstractFilterType implements FilterTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver
            ->setDefaults(array(
                'transformer_id' => 'lexik_form_filter.transformer.default',
            ))
            ->setAllowedValues(array(
                'transformer_id' => array('lexik_form_filter.transformer.default'),
            ))                
            ;
    }    
    
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
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
    public function applyFilter(QueryBuilder $queryBuilder, Expr $expr, $field, array $values)
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
                    $queryBuilder->andWhere($expr->in($field, $ids));
                }

            } else {
                if (!is_callable(array($values['value'], 'getId'))) {
                    throw new \Exception(sprintf('Can\'t call method "getId()" on an instance of "%s"', get_class($values['value'])));
                }

                $queryBuilder->andWhere($expr->eq($field, $values['value']->getId()));
            }
        }
    }
}
