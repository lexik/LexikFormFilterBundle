<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Transformer;

use Symfony\Component\Form\FormInterface;

/**
 * Transform data into default format
 *
 * @author <g.gauthier@lexik.com>
 *
 */
class FilterDefaultTransformer implements FilterTransformerInterface
{
    /**
     * {@inheritDoc}
     * @see Lexik\Bundle\FormFilterBundle\Filter\Transformer.FilterTransformerInterface::transform()
     */
    public function transform(FormInterface $form)
    {
        return array('value' => $form->getData());
    }
}
