<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\EmbeddedFilterInterface;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class OptionFilterType extends AbstractType implements EmbeddedFilterInterface
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('label', 'filter_text');
        $builder->add('rank', 'filter_number');
    }

    public function getName()
    {
        return 'options_filter';
    }
}