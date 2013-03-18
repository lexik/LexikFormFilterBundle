<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

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
        $builder->add('options', new OptionFilterType());
    }

    public function getName()
    {
        return 'item_filter';
    }
}
