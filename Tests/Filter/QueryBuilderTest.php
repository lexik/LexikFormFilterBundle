<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter;

use Symfony\Component\HttpFoundation\Request;

use Lexik\Bundle\FormFilterBundle\Filter\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Tests\TestCase;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemFilterType;

/**
 * Filter query builder tests.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class QueryBuilderTest extends TestCase
{
    public function testBuildQuery()
    {
        $form = $this->formFactory->create(new ItemFilterType());
        $filterQueryBuilder = new QueryBuilder();

        // without binding the form
        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
        $this->assertEquals(array(), $doctrineQueryBuilder->getParameters());

        // bind a request to the form - 1 params
        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $request = $this->craeteRequest(array('name' => 'blabla', 'position' => ''));
        $form->bindRequest($request);

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name = :name_param';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
        $this->assertEquals(array('name_param' => 'blabla'), $doctrineQueryBuilder->getParameters());

        // bind a request to the form - 2 params
        $form = $this->formFactory->create(new ItemFilterType(false, true));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $request = $this->craeteRequest(array('name' => 'blabla', 'position' => 2));
        $form->bindRequest($request);

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name <> :name_param AND i.position = :position_param';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
        $this->assertEquals(array('name_param' => 'blabla', 'position_param' => 2), $doctrineQueryBuilder->getParameters());

        // use filter type options
        $form = $this->formFactory->create(new ItemFilterType(true, true));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $request = $this->craeteRequest(array('name' => array('text' => 'blabla', 'condition_pattern' => '%s%%'), 'position' => 2));
        $form->bindRequest($request);

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name LIKE :name_param AND i.position <> :position_param';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
        $this->assertEquals(array('name_param' => 'blabla%', 'position_param' => 2), $doctrineQueryBuilder->getParameters());
    }

    protected function createDoctrineQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->select('i')
            ->from('Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity', 'i');
    }

    protected function craeteRequest($values)
    {
        return new Request(
            array(),
            array('item_filter' => $values),
            array(),
            array(),
            array(),
            array('REQUEST_METHOD' => 'POST')
        );
    }
}