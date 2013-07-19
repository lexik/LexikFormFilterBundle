<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ItemFilterType extends AbstractType
{
    protected $withSelector;
    protected $checkbox;
    protected $datetime;

    public function __construct($withSelector = false, $checkbox = false, $datetime = false)
    {
        $this->withSelector = $withSelector;
        $this->checkbox = $checkbox;
        $this->datetime = $datetime;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$this->withSelector) {
            $builder->add('name', 'filter_text');
            $builder->add('position', 'filter_number', array(
                'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN,
            ));
        } else {
            $builder->add('name', 'filter_text', array(
                'condition_pattern' => FilterOperands::OPERAND_SELECTOR,
            ));
            $builder->add('position', 'filter_number', array(
                'condition_operator' => FilterOperands::OPERAND_SELECTOR,
            ));
        }

        $builder->add('enabled', $this->checkbox ? 'filter_checkbox' : 'filter_boolean');
        $builder->add('createdAt', $this->datetime ? 'filter_datetime' : 'filter_date');
    }

    public function getName()
    {
        return 'item_filter';
    }
}
