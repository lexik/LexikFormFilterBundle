<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Lexik\Bundle\FormFilterBundle\Filter\Expr;

use Doctrine\ORM\QueryBuilder;

/**
 * Filter type.
 *
 * @author Roman Marintsenko <roman.marintsenko@knplabs.com>
 */
abstract class AbstractFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        if ($options['apply_filter'] instanceof \Closure || is_callable($options['apply_filter'])) {
            $builder->setAttribute('apply_filter', $options['apply_filter']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
             'required'     => false,
             'apply_filter' => null,
        ));
    }
}
