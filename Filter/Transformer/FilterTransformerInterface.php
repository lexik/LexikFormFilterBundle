<?php

namespace Lexik\Bundle\FormFilterBundle\Filter\Transformer;

use Symfony\Component\Form\FormInterface;

/**
 * This interface allows the implementation of a transform filter
 *
 * @author <g.gauthier@lexik.com>
 */
interface FilterTransformerInterface
{
    /**
     * Transform data of a form into value manipulate by FilterBuilderUpdater
     *
     * @param FormInterface $form
     *
     * @return array
     */
    public function transform(FormInterface $form);
}
