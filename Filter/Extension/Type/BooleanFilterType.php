<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Lexik\Bundle\FormFilterBundle\Filter\Expr;

use Doctrine\ORM\QueryBuilder;

/**
 * Filter to use with boolean values.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class BooleanFilterType extends AbstractType implements FilterTypeInterface
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
        return 'filter_choice';
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

        $resolver->setDefaults(array(
            'choices'     => array(
                self::VALUE_YES  => $this->trans('boolean.yes'),
                self::VALUE_NO   => $this->trans('boolean.no'),
            ),
            'empty_value' => $this->trans('boolean.yes_or_no'),
        ));
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

    /**
     * {@inheritdoc}
     */
    public function getTransformerId()
    {
        return 'lexik_form_filter.transformer.default';
    }

    /**
     * {@inheritdoc}
     */
    public function applyFilter(QueryBuilder $queryBuilder, Expr $expr, $field, array $values)
    {
        if (!empty($values['value'])) {
            $value = (int)(self::VALUE_YES == $values['value']);
            $queryBuilder->andWhere($expr->eq($field, $value));
        }
    }
}