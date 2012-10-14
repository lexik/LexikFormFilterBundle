<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

//@todo remove constants this namespace
use Lexik\Bundle\FormFilterBundle\Filter\ORM\Expr;

/**
 * Filter type for strings.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class TextFilterType extends AbstractFilterType
{
    const PATTERN_EQUALS     = Expr::STRING_EQ;
    const PATTERN_START_WITH = Expr::STRING_STARTS;
    const PATTERN_END_WITH   = Expr::STRING_ENDS;
    const PATTERN_CONTAINS   = Expr::STRING_BOTH;

    const SELECT_PATTERN = 'select_pattern';


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {       
        parent::buildForm($builder, $options);
        

        if (true === $options['compound']) {
            $builder->add('condition_pattern', 'choice', $options['choice_options']);
            $builder->add('text', 'text', $options['text_options']);

        } else {
            $builder->setAttribute('filter_options', array(
                'condition_pattern' => $options['condition_pattern'],
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
            return $options['condition_pattern'] == TextFilterType::SELECT_PATTERN;
        };
        
        
        $transformerId = function (Options $options) {
            return $options['compound'] ? 'lexik_form_filter.transformer.text' : 'lexik_form_filter.transformer.default';
        };

        
        $resolver
            ->setDefaults(array(
                'condition_pattern' => self::PATTERN_EQUALS,
                'compound'          => $compound,
                'text_options'      => array(
                    'required' => false,
                    'trim'     => true,
                 ),
                'choice_options'    => array(
                   'choices'  => self::getConditionChoices(),
                   'required' => false,
                 ),
                'transformer_id' => $transformerId,
            ))
            ->setAllowedValues(array(
                'transformer_id' => array('lexik_form_filter.transformer.text','lexik_form_filter.transformer.default'),
            ))
        ;
        
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_text';
    }

    /**
     * Retruns an array of available conditions patterns.
     *
     * @return array
     */
    static public function getConditionChoices()
    {
        $choices = array();

        $reflection = new \ReflectionClass(__CLASS__);
        foreach ($reflection->getConstants() as $name => $value) {
            if ('PATTERN_' === substr($name, 0, 8)) {
                $choices[$value] = strtolower(str_replace(array('PATTERN_', '_'), array('', ' '), $name));
            }
        }

        return $choices;
    }
}
