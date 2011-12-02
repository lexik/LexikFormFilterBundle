<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterTextType;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\FilterNumberType;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ItemFilterType extends AbstractType
{
    protected $addTypeOptions;

    public function __construct($addTypeOptions = false)
    {
        $this->addTypeOptions = $addTypeOptions;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        if (!$this->addTypeOptions) {
            $builder->add('name', 'filter_text');
            $builder->add('position', 'filter_number');
        } else {
            $builder->add('name', 'filter_text', array(
                'condition_pattern' => FilterTextType::SELECT_PATTERN,
            ));
            $builder->add('position', 'filter_number', array(
                'condition_operator' => FilterNumberType::OPERATOR_GREATER_THAN,
            ));
        }
    }

    public function getName()
    {
        return 'item_filter';
    }
}