<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Transformer;

use Lexik\Bundle\FormFilterBundle\Filter\Transformer\FilterTransformerInterface;

use Symfony\Component\Form\FormInterface;

/**
 * Transform a filter text into a right format
 * @author <g.gauthier@lexik.com>
 *
 */
class FilterTextTransformer implements FilterTransformerInterface
{
    /**
     * {@inheritDoc}
     * @see Lexik\Bundle\FormFilterBundle\Filter\Transformer.FilterTransformerInterface::transform()
     */
    public function transform(FormInterface $form)
    {
        $data   = $form->getData();
        $values = array('value' => array());

        if (array_key_exists('text', $data)) {
            $values = array('value' => $data['text']);
            $values += $data;
        }

        return $values;
    }
}
