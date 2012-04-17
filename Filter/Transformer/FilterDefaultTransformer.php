<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Transformer;

use Symfony\Component\Form\Form;

/**
 * Transform data into default format
 *
 * @author <g.gauthier@lexik.com>
 *
 */
class FilterDefaultTransformer implements FilterTransformerInterface
{
    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\FormFilterBundle\Filter\Transformer.FilterTransformerInterface::transform()
     */
    public function transform(Form $form)
    {
        return array('value' => $form->getData());
    }
}
