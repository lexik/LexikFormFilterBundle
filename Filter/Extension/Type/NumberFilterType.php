<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilder;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;

/**
 * Filter type for numbers.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class NumberFilterType extends NumberType implements FilterTypeInterface
{
    const OPERATOR_EQUAL              = 'eq';
    const OPERATOR_GREATER_THAN       = 'gt';
    const OPERATOR_GREATER_THAN_EQUAL = 'gte';
    const OPERATOR_LOWER_THAN         = 'lt';
    const OPERATOR_LOWER_THAN_EQUAL   = 'lte';

    const SELECT_OPERATOR = 'select_operator';

    /**
     * @var string
     */
    protected $transformerId;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $attributes = array();
        $this->transformerId = 'lexik_form_filter.transformer.default';

        if ($options['condition_operator'] == self::SELECT_OPERATOR) {
            $this->transformerId = 'lexik_form_filter.transformer.text';

            $numberOptions = array_intersect_key($options, parent::getDefaultOptions(array()));
            $numberOptions['required'] = isset($options['required']) ? $options['required'] : false;
            $numberOptions['trim'] = isset($options['trim']) ? $options['trim'] : true;

            $builder->add('condition_operator', 'choice', array(
                'choices' => self::getOperatorChoices(),
            ));
            $builder->add('text', 'number', $numberOptions);
        } else {
            parent::buildForm($builder, $options);

            $attributes['condition_operator'] = $options['condition_operator'];
        }

        $builder->setAttribute('filter_options', $attributes);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        $options = parent::getDefaultOptions();
        $options['condition_operator'] = self::OPERATOR_EQUAL;

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(array $options)
    {
        return ($options['condition_operator'] == self::SELECT_OPERATOR) ? 'filter' : 'filter_field';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_number';
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformerId()
    {
        return $this->transformerId;
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(QueryBuilder $queryBuilder, Expr $e, $field, $values)
    {
        if (!empty($values['value'])) {
            $op = $values['condition_operator'];
            $queryBuilder->andWhere($e->$op($field, $values['value']));
        }
    }

    /**
     * Retruns an array of available conditions operator.
     *
     * @return array
     */
    static public function getOperatorChoices()
    {
        $choices = array();

        $reflection = new \ReflectionClass(__CLASS__);
        foreach ($reflection->getConstants() as $name => $value) {
            if ('OPERATOR_' === substr($name, 0, 9)) {
                $choices[$value] = strtolower(str_replace(array('OPERATOR_', '_'), array('', ' '), $name));
            }
        }

        return $choices;
    }
}