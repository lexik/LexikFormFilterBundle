<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter to use with boolean values.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class BooleanFilterType extends AbstractFilterType
{
    const VALUE_YES = 'y';
    const VALUE_NO  = 'n';

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    protected $translator;

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_boolean';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver
            ->setDefaults(array(
                'choices'     => array(
                    self::VALUE_YES  => $this->trans('boolean.yes'),
                    self::VALUE_NO   => $this->trans('boolean.no'),
                ),
                'empty_value' => $this->trans('boolean.yes_or_no'),
                'transformer_id' => 'lexik_form_filter.transformer.default',
            ))
            ->setAllowedValues(array(
                'transformer_id' => array('lexik_form_filter.transformer.default'),
            ))
        ;
    }

    /**
     * Set Translator
     *
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Translate a key for a domain
     *
     * @param string $key
     * @param array  $parameters
     * @param string $domain
     *
     * @return string
     */
    private function trans($key, array $parameters = array(), $domain = 'LexikFormFilterBundle')
    {
        return ($this->translator instanceof TranslatorInterface)
            ? $this->translator->trans($key, $parameters, $domain)
            : $key;
    }
}