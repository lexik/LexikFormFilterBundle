<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Transformer;

use Symfony\component\form\Form;

/**
 * This interface allows the implementation of a transform filter
 *
 * @author <g.gauthier@lexik.com>
 */
interface FilterTransformerInterface
{
    /**
     * Transform data of a form into value manipulate by QueryBuilder
     *
     * @param Form $form
     *
     * @return array
     */
    public function transform(Form $form);
}
