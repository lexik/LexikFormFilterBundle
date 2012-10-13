<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\TextFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\NumberFilterType;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ItemFilterType extends AbstractType
{
    protected $withSelector;
    protected $checkbox;

    public function __construct($withSelector = false, $checkbox = false)
    {
        $this->withSelector = $withSelector;
        $this->checkbox = $checkbox;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$this->withSelector) {
            $builder->add('name', 'filter_text');
            $builder->add('position', 'filter_number', array(
                'condition_operator' => NumberFilterType::OPERATOR_GREATER_THAN,
            ));
        } else {
            $builder->add('name', 'filter_text', array(
                'condition_pattern' => TextFilterType::SELECT_PATTERN,
            ));
            $builder->add('position', 'filter_number', array(
                'condition_operator' => NumberFilterType::SELECT_OPERATOR,
            ));
        }

        $builder->add('enabled', $this->checkbox ? 'filter_checkbox' : 'filter_boolean');
        $builder->add('createdAt', 'filter_date');
    }

    public function getName()
    {
        return 'item_filter';
    }
}