<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Transformer;

use Symfony\Component\Form\Form;

/**
 * Transform data with filter_value_key's attributes into a right format
 *
 * @author <g.gauthier@lexik.com>
 *
 */
class FilterValueKeysTransformer implements FilterTransformerInterface
{
    /**
     * (non-PHPdoc)
     * @see Lexik\Bundle\FormFilterBundle\Filter\Transformer.FilterTransformerInterface::transform()
     */
    public function transform(Form $form)
    {
        $data = $form->getData();
        $keys = null;

        if ($form->hasAttribute('filter_value_keys')) {
            $keys = array_merge($data, $form->getAttribute('filter_value_keys'));
        }
        $values = array('value' => array());

        foreach ($keys as $key => $value) {
            $values['value'][$key][] = $data[$key];
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    $values['value'][$key][$k] = $v;
                }
            }
        }

        return $values;
    }
}

