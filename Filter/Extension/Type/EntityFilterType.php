<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Extension\Type;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Filter type for related entities.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class EntityFilterType extends AbstractFilterType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver
            ->setDefaults(array(
                'transformer_id' => 'lexik_form_filter.transformer.default',
            ))
            ->setAllowedValues(array(
                'transformer_id' => array('lexik_form_filter.transformer.default'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'filter_entity';
    }
}
