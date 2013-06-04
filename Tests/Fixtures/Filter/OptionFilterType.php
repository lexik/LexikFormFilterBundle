<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr;

use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTypeSharedableInterface;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class OptionFilterType extends AbstractType implements FilterTypeSharedableInterface
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('label', 'filter_text');
        $builder->add('rank', 'filter_number');
    }

    public function getName()
    {
        return 'options_filter';
    }

    public function addShared(FilterBuilderExecuterInterface $qbe)
    {
        $qbe->addOnce($qbe->getAlias().'.options', 'opt', function(QueryBuilder $filterBuilder, $alias, $joinAlias, Expr $expr) {
            $filterBuilder->leftJoin($alias . '.options', 'opt');
        });
    }
}
