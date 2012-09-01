<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Lexik\Bundle\FormFilterBundle\Filter\Expr;

/**
 * Filter type for numbers.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class NumberFilterType extends AbstractFilterType implements FilterTypeInterface
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
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $this->transformerId = 'lexik_form_filter.transformer.default';

        if (true === $options['compound']) {
            // if the form is compound we don't need the NumberToLocalizedStringTransformer added in the parent type.
            $builder->resetViewTransformers();

            $builder->add('condition_operator', 'choice', $options['choice_options']);
            $builder->add('text', 'number', $options['number_options']);

            $this->transformerId = 'lexik_form_filter.transformer.text';
        } else {
            $builder->setAttribute('filter_options', array(
                'condition_operator' => $options['condition_operator'],
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $compound = function (Options $options) {
            return $options['condition_operator'] == NumberFilterType::SELECT_OPERATOR;
        };

        $resolver->setDefaults(array(
            'condition_operator' => self::OPERATOR_EQUAL,
            'compound'           => $compound,
            'number_options'     => array(
                'required' => false,
            ),
            'choice_options'     => array(
                'choices'  => self::getOperatorChoices(),
                'required' => false,
            ),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'number';
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
    public function applyFilter(QueryBuilder $queryBuilder, Expr $expr, $field, array $values)
    {
        if (!empty($values['value'])) {
            $op = $values['condition_operator'];
            $queryBuilder->andWhere($expr->$op($field, $values['value']));
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
