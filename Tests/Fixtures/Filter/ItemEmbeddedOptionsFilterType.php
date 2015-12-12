<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Doctrine\MongoDB\Query\Builder;
use Doctrine\MongoDB\Query\Expr as MongoExpr;
use Doctrine\ORM\Query\Expr as ORMExpr;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderExecuterInterface;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\CollectionAdapterFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\NumberFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ItemEmbeddedOptionsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ('mongo' === $options['doctrine_builder']) {
            $addShared = function (FilterBuilderExecuterInterface $qbe) {
                $qbe->addOnce('options', 'options', null);
            };
        } else {
            $addShared = function (FilterBuilderExecuterInterface $qbe) {
                $joinClosure = function (QueryBuilder $filterBuilder, $alias, $joinAlias, ORMExpr $expr) {
                    $filterBuilder->leftJoin($alias . '.options', $joinAlias);
                };
                $qbe->addOnce($qbe->getAlias().'.options', 'opt', $joinClosure);
            };
        }

        $builder->add('name', TextFilterType::class);
        $builder->add('position', NumberFilterType::class);
        $builder->add('options', CollectionAdapterFilterType::class, array(
            'entry_type' => OptionFilterType::class,
            'add_shared' => $addShared,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'doctrine_builder' => null,
        ));
    }

    public function getBlockPrefix()
    {
        return 'item_filter';
    }
}
