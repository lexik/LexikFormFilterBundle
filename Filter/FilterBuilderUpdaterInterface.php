<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Symfony\Component\Form\FormInterface;

interface FilterBuilderUpdaterInterface
{
    /**
     * Build a filter query.
     *
     * @param  FormInterface $form
     * @param  object $filterBuilder
     * @param  string|null $alias
     */
    public function addFilterConditions(FormInterface $form, $filterBuilder, $alias = null);
}
