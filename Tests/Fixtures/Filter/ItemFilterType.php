<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\TextFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\NumberFilterType;

/**
 * Form filter for tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class ItemFilterType extends AbstractType
{
    protected $addTypeOptions;
    protected $withCallback;

    public function __construct($addTypeOptions = false, $withCallback = false)
    {
        $this->addTypeOptions = $addTypeOptions;
        $this->withCallback = $withCallback;
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        if (!$this->addTypeOptions) {
            $builder->add('name', 'filter_text', array(
                'apply_filter' => $this->withCallback ? array($this, 'fieldNameCallback') : null,
            ));
            $builder->add('position', 'filter_number');
        } else {
            $builder->add('name', 'filter_text', array(
                'condition_pattern' => TextFilterType::SELECT_PATTERN,
            ));
            $builder->add('position', 'filter_number', array(
                'condition_operator' => NumberFilterType::OPERATOR_GREATER_THAN,
                'apply_filter' => $this->withCallback ? function($queryBuilder, $field, $values) {
                    if (!empty($values['value'])) {
                        $paramName = sprintf('%s_param', $field);
                        $condition = sprintf('%s.%s <> :%s',
                            $queryBuilder->getRootAlias(),
                            $field,
                            $paramName
                        );

                        $queryBuilder->andWhere($condition)
                            ->setParameter($paramName, $values['value']);
                    }
                } : null,
            ));
        }
    }

    public function getName()
    {
        return 'item_filter';
    }

    public function fieldNameCallback($queryBuilder, $field, $values)
    {
        if (!empty($values['value'])) {
            $paramName = sprintf('%s_param', $field);
            $value = sprintf($values['condition_pattern'], $values['value']);
            $condition = sprintf('%s.%s <> :%s',
                $queryBuilder->getRootAlias(),
                $field,
                $paramName
            );

            $queryBuilder->andWhere($condition)
                ->setParameter($paramName, $value, \PDO::PARAM_STR);
        }
    }
}