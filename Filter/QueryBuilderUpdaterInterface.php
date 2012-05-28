<?php

namespace Lexik\Bundle\FormFilterBundle\Filter;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;

interface QueryBuilderUpdaterInterface
{

    /**
     * Build a filter query.
     *
     * @param  FormInterface $form
     * @param  QueryBuilder $queryBuilder
     * @return QueryBuilder
     */
    public function addFilterConditions(FormInterface $form, QueryBuilder $queryBuilder);

}
