<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilder;

use Doctrine\ORM\QueryBuilder;

/**
 * Filter type for numbers.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class NumberFilterType extends NumberType implements FilterTypeInterface
{
    const OPERATOR_EQUAL              = '=';
    const OPERATOR_GREATER_THAN       = '>';
    const OPERATOR_GREATER_THAN_EQUAL = '>=';
    const OPERATOR_LOWER_THAN_        = '<';
    const OPERATOR_LOWER_THAN_EQUAL   = '<=';

    const SELECT_OPERATOR = 'select_operator';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $attributes = array();

        if ($options['condition_operator'] == self::SELECT_OPERATOR) {
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
    public function getDefaultOptions(array $options)
    {
        $options = parent::getDefaultOptions($options);
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

    public function getTransformerId()
    {
        return 'lexik_filter.transformer.default';
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(QueryBuilder $queryBuilder, $field, $values)
    {
        if (!empty($values['value'])) {
            $paramName = sprintf('%s_param', $field);
            $condition = sprintf('%s.%s %s :%s',
                $queryBuilder->getRootAlias(),
                $field,
                $values['condition_operator'],
                $paramName
            );

            $queryBuilder->andWhere($condition)
                ->setParameter($paramName, $values['value']);
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