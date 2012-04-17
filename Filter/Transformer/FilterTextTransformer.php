<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Transformer;

use Lexik\Bundle\FormFilterBundle\Filter\Transformer\FilterTransformerInterface;

use Symfony\Component\Form\Form;

/**
 * Transform a filter text into a right format
 * @author <g.gauthier@lexik.com>
 *
 */
class FilterTextTransformer implements FilterTransformerInterface
{
    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\FormFilterBundle\Filter\Transformer.FilterTransformerInterface::transform()
     */
    public function transform(Form $form)
    {
        $data = $form->getData();
        $keys = null;
        $values = array('value' => array());

        if (array_key_exists('text', $data)) {
            $values = array('value' => $data['text']);
            $values += $data;
        }

        return $values;
    }
}

