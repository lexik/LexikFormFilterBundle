<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter;

use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\BooleanFilterType;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\Request;

use Lexik\Bundle\FormFilterBundle\DependencyInjection\LexikFormFilterExtension;
use Lexik\Bundle\FormFilterBundle\DependencyInjection\Compiler\FilterTransformerCompilerPass;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\NumberFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Extension\Type\TextFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\Transformer\TransformerAggregator;
use Lexik\Bundle\FormFilterBundle\Filter\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Tests\TestCase;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\OtherFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemCallbackFilterType;
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
        $filterQueryBuilder = $this->initQueryBuilder();

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
        $form = $this->formFactory->create(new ItemFilterType());

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $request = $this->craeteRequest(array('name' => 'blabla', 'position' => 2));
        $form->bindRequest($request);

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name = :name_param AND i.position > :position_param';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
        $this->assertEquals(array('name_param' => 'blabla', 'position_param' => 2), $doctrineQueryBuilder->getParameters());

        // bind a request to the form - 3 params
        $form = $this->formFactory->create(new ItemFilterType());

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $request = $this->craeteRequest(array('name' => 'blabla', 'position' => 2, 'enabled' => BooleanFilterType::VALUE_YES));
        $form->bindRequest($request);

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name = :name_param AND i.position > :position_param AND i.enabled = :enabled_param';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
        $this->assertEquals(array('name_param' => 'blabla', 'position_param' => 2, 'enabled_param' => 1), $doctrineQueryBuilder->getParameters());

        // bind a request to the form - 3 params (use checkbox for enabled field)
        $form = $this->formFactory->create(new ItemFilterType(false, true));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $request = $this->craeteRequest(array('name' => 'blabla', 'position' => 2, 'enabled' => 'yes'));
        $form->bindRequest($request);

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name = :name_param AND i.position > :position_param AND i.enabled = :enabled_param';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
        $this->assertEquals(array('name_param' => 'blabla', 'position_param' => 2, 'enabled_param' => 1), $doctrineQueryBuilder->getParameters());

        // bind a request to the form - 2 params + pattern selector
        $form = $this->formFactory->create(new ItemFilterType(true));

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $request = $this->craeteRequest(array(
            'name' => array('text' => 'blabla', 'condition_pattern' => TextFilterType::PATTERN_END_WITH),
            'position' => array('text' => 2, 'condition_operator' => NumberFilterType::OPERATOR_LOWER_THAN_EQUAL))
        );
        $form->bindRequest($request);

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name LIKE :name_param AND i.position <= :position_param';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
        $this->assertEquals(array('name_param' => '%blabla', 'position_param' => 2), $doctrineQueryBuilder->getParameters());

        // use apply_filter option
        $form = $this->formFactory->create(new ItemCallbackFilterType());

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $request = $this->craeteRequest(array('name' => 'blabla', 'position' => 2));
        $form->bindRequest($request);

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.name <> :name_param AND i.position <> :position_param';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
        $this->assertEquals(array('name_param' => 'blabla', 'position_param' => 2), $doctrineQueryBuilder->getParameters());
    }

    public function testNumberRange()
    {
        // use filter type options
        $form = $this->formFactory->create(new OtherFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $request = $this->craeteRequest(array('position' => array('left_number' => 1, 'right_number' => 3)));
        $form->bindRequest($request);

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE (i.position > :left_position_param AND i.position < :right_position_param)';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
    }

    public function testDateRange()
    {
        // use filter type options
        $form = $this->formFactory->create(new OtherFilterType());
        $filterQueryBuilder = $this->initQueryBuilder();

        $doctrineQueryBuilder = $this->createDoctrineQueryBuilder();
        $request = $this->craeteRequest(array('createdAt' => array(
                'left_date' => array('year' => '2012', 'month' => '05', 'day' => '12'),
                'right_date' => array('year' => '2012', 'month' => '05', 'day' => '22')))
                );

        $form->bindRequest($request);

        $expectedDql = 'SELECT i FROM Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity i WHERE i.createdAt >= :left_createdAt_param AND i.createdAt <= :right_createdAt_param';
        $filterQueryBuilder->buildQuery($form, $doctrineQueryBuilder);
        $this->assertEquals($expectedDql, $doctrineQueryBuilder->getDql());
    }

    protected function createDoctrineQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->select('i')
            ->from('Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Entity', 'i');
    }

    protected function initQueryBuilder()
    {
        $container = $this->getContainer();
        return $container->get('lexik_form_filter.query_builder');
    }

    protected function getContainer()
    {
        $container = new ContainerBuilder();
        $filter = new LexikFormFilterExtension();
        $container->registerExtension($filter);
        $loadXml = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../Resources/config'));
        $loadXml->load('services.xml');

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->addCompilerPass(new FilterTransformerCompilerPass());
        $container->compile();

        return $container;
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