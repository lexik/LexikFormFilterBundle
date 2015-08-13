<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter\Doctrine;

use Doctrine\Bundle\MongoDBBundle\DataCollector\PrettyDataCollector;
use Doctrine\MongoDB\Query\Query;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\QueryBuilderUpdater;
use Lexik\Bundle\FormFilterBundle\Tests\TestCase;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemFilterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mongodb query builder tests.
 */
class MongodbQueryBuilderUpdaterTest extends TestCase
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * @var PrettyDataCollector
     */
    protected $collector;

    public function setUp()
    {
        parent::setUp();

        // log queries to compare them as bson
        $this->collector = new PrettyDataCollector();
        $this->dm = $this->getMongodbDocumentManager(array($this->collector, 'logQuery'));
    }

    public function testBuildQuery()
    {
        $bson = array(
            'db.items.find();',
            'db.items.find({ "$and": [ { "name": "blabla" } ] });',
            'db.items.find({ "$and": [ { "name": "blabla" }, { "position": { "$gt": 2 } } ] });',
            'db.items.find({ "$and": [ { "name": "blabla" }, { "position": { "$gt": 2 } }, { "enabled": true } ] });',
            'db.items.find({ "$and": [ { "name": "blabla" }, { "position": { "$gt": 2 } }, { "enabled": true } ] });',
            'db.items.find({ "$and": [ { "name": new RegExp(".*blabla$", "i") }, { "position": { "$lte": 2 } }, { "createdAt": new ISODate("2013-09-27T00:00:00+02:00") } ] });',
            'db.items.find({ "$and": [ { "name": new RegExp(".*blabla$", "i") }, { "position": { "$lte": 2 } }, { "createdAt": new ISODate("2013-09-27T13:21:00+02:00") } ] });',
        );

        $form = $this->formFactory->create(new ItemFilterType());
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        // without binding the form
        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[0], $this->toBson($mongoQB->getQuery()));

        // bind a request to the form - 1 params
        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => ''));

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[1], $this->toBson($mongoQB->getQuery()));

        // bind a request to the form - 2 params
        $form = $this->formFactory->create(new ItemFilterType());

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2));

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[2], $this->toBson($mongoQB->getQuery()));

        // bind a request to the form - 3 params
        $form = $this->formFactory->create(new ItemFilterType());

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2, 'enabled' => BooleanFilterType::VALUE_YES));

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[3], $this->toBson($mongoQB->getQuery()));

        // bind a request to the form - 3 params (use checkbox for enabled field)
        $form = $this->formFactory->create(new ItemFilterType(), null, array(
            'checkbox' => true,
        ));

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2, 'enabled' => 'yes'));

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[4], $this->toBson($mongoQB->getQuery()));

        // bind a request to the form - date + pattern selector
        $form = $this->formFactory->create(new ItemFilterType(), null, array(
            'with_selector' => true,
        ));

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'name'      => array('text' => 'blabla', 'condition_pattern' => FilterOperands::STRING_ENDS),
            'position'  => array('text' => 2, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
            'createdAt' => array('year' => 2013, 'month' => 9, 'day' => 27),
        ));

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[5], $this->toBson($mongoQB->getQuery()));

        // bind a request to the form - datetime + pattern selector
        $form = $this->formFactory->create(new ItemFilterType(), null, array(
            'with_selector' => true,
            'datetime'      => true,
        ));

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'name'      => array('text' => 'blabla', 'condition_pattern' => FilterOperands::STRING_ENDS),
            'position'  => array('text' => 2, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
            'createdAt' => array(
                'date' => array('year' => 2013, 'month' => 9, 'day' => 27),
                'time' => array('hour' => 13, 'minute' => 21),
            ),
        ));

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[6], $this->toBson($mongoQB->getQuery()));
    }

    protected function createDoctrineQueryBuilder()
    {
        return $this->dm
            ->getRepository('Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Document\Item')
            ->createQueryBuilder();
    }

    protected function toBson(Query $query)
    {
        $query->execute();

        $this->collector->collect(new Request(), new Response());
        $q = $this->collector->getQueries();

        array_shift($q);

        return isset($q[count($q)-1]) ? $q[count($q)-1] : null;
    }
}
