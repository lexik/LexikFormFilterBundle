<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ItemEmbeddedOptionsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'filter_text');
        $builder->add('position', 'filter_number');
        $builder->add('options', 'filter_collection_adapter', array(
            'type'       => new OptionFilterType(),
            'add_shared' => function (FilterBuilderExecuterInterface $qbe) {
                $joinClosure = function(QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
                    $filterBuilder->leftJoin($alias . '.options', $joinAlias);
                };
                $qbe->addOnce($qbe->getAlias().'.options', 'opt', $joinClosure);
            }
        ));
    }

    public function getName()
    {
        return 'item_filter';
    }
}
