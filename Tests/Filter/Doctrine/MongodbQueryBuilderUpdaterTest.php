<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter\Doctrine;

use Doctrine\Bundle\MongoDBBundle\DataCollector\PrettyDataCollector;
use Doctrine\MongoDB\Query\Query;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionBuilderInterface;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\FormType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemCallbackFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemEmbeddedOptionsFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\RangeFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Filter\QueryBuilderUpdater;
use Lexik\Bundle\FormFilterBundle\Tests\TestCase;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemFilterType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
            '#db.items.find\(\{ "\$and": \[ \{ "name": new RegExp\("\.\*blabla\$", "i"\) \}, \{ "position": \{ "\$lte": 2 \} \}, \{ "createdAt": new ISODate\("2013-09-27T00:00:00\+[0-9:]+"\) \} \] \}\);#',
            '#db.items.find\(\{ "\$and": \[ \{ "name": new RegExp\("\.\*blabla\$", "i"\) \}, \{ "position": \{ "\$lte": 2 \} \}, \{ "createdAt": new ISODate\("2013-09-27T13:21:00\+[0-9:]+"\) \} \] \}\);#',
        );

        $form = $this->formFactory->create(ItemFilterType::class);
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
        $form = $this->formFactory->create(ItemFilterType::class);

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2));

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[2], $this->toBson($mongoQB->getQuery()));

        // bind a request to the form - 3 params
        $form = $this->formFactory->create(ItemFilterType::class);

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2, 'enabled' => BooleanFilterType::VALUE_YES));

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[3], $this->toBson($mongoQB->getQuery()));

        // bind a request to the form - 3 params (use checkbox for enabled field)
        $form = $this->formFactory->create(ItemFilterType::class, null, array(
            'checkbox' => true,
        ));

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(array('name' => 'blabla', 'position' => 2, 'enabled' => 'yes'));

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[4], $this->toBson($mongoQB->getQuery()));

        // bind a request to the form - date + pattern selector
        $form = $this->formFactory->create(ItemFilterType::class, null, array(
            'with_selector' => true,
        ));

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(array(
            'name'      => array('text' => 'blabla', 'condition_pattern' => FilterOperands::STRING_ENDS),
            'position'  => array('text' => 2, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
            'createdAt' => array('year' => 2013, 'month' => 9, 'day' => 27),
        ));

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertRegExp($bson[5], $this->toBson($mongoQB->getQuery()));

        // bind a request to the form - datetime + pattern selector
        $form = $this->formFactory->create(ItemFilterType::class, null, array(
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
        $this->assertRegExp($bson[6], $this->toBson($mongoQB->getQuery()));
    }

    public function testDisabledFieldQuery()
    {
        $form = $this->formFactory->create(ItemFilterType::class, null, array(
            'with_selector' => false,
            'disabled_name' => true,
        ));
        $form->submit(array('name' => 'blabla', 'position' => 2));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$and": [ { "position": { "$gt": 2 } } ] });',
            $this->toBson($mongoQB->getQuery())
        );
    }

    public function testApplyFilterOption()
    {
        $form = $this->formFactory->create(ItemCallbackFilterType::class);
        $form->submit(array('name' => 'blabla', 'position' => 2));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$and": [ { "name": { "$ne": "blabla" } }, { "position": { "$ne": 2 } } ] });',
            $this->toBson($mongoQB->getQuery())
        );
    }

    public function testNumberRange()
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(array('position' => array('left_number' => 1, 'right_number' => 3)));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$and": [ { "position": { "$gt": 1, "$lt": 3 } } ] });',
            $this->toBson($mongoQB->getQuery())
        );
    }

    public function testNumberRangeWithSelector()
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(array('position_selector' => array(
            'left_number'  => array('text' => 4, 'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN),
            'right_number' => array('text' => 8, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL),
        )));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$and": [ { "position_selector": { "$gt": 4, "$lte": 8 } } ] });',
            $this->toBson($mongoQB->getQuery())
        );
    }

    public function testNumberRangeDefaultValues()
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(array('default_position' => array('left_number' => 1, 'right_number' => 3)));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$and": [ { "default_position": { "$gte": 1, "$lte": 3 } } ] });',
            $this->toBson($mongoQB->getQuery())
        );
    }

    public function testDateRange()
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(array(
            'createdAt' => array(
                'left_date'  => '2012-05-12',
                'right_date' => array('year' => '2012', 'month' => '5', 'day' => '22'),
            ),
        ));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertRegExp(
            '#db.items.find\(\{ "\$and": \[ \{ "createdAt": \{ "\$gte": new ISODate\("2012-05-12T00:00:00\+[0-9:]+"\), "\$lt": new ISODate\("2012-05-22T00:00:00\+[0-9:]+"\) \} \} \] \}\);#',
            $this->toBson($mongoQB->getQuery())
        );
    }

    public function testDateTimeRange()
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(array(
            'updatedAt' => array(
                'left_datetime' => array(
                    'date' => '2012-05-12',
                    'time' => '14:55',
                ),
                'right_datetime' => array(
                    'date' => array('year' => '2012', 'month' => '6', 'day' => '10'),
                    'time' => array('hour' => 22, 'minute' => 12),
                ),
            ),
        ));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertRegExp(
            '#db\.items\.find\(\{ "\$and": \[ \{ "updatedAt": \{ "\$gte": new ISODate\("2012-05-12T14:55:00\+[0-9:]+"\), "\$lt": new ISODate\("2012-06-10T22:12:00\+[0-9:]+"\) \} \} \] }\);#',
            $this->toBson($mongoQB->getQuery())
        );
    }

    public function testFilterStandardType()
    {
        $form = $this->formFactory->create(FormType::class);
        $form->submit(array(
            'name'     => 'hey dude',
            'position' => 99,
        ));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$and": [ { "name": new RegExp(".*hey dude.*", "i") }, { "position": 99 } ] });',
            $this->toBson($mongoQB->getQuery())
        );
    }

    public function testEmbedFormFilter()
    {
        // doctrine query builder without any joins
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, array(
            'doctrine_builder' => 'mongo',
        ));
        $form->submit(array('name' => 'dude', 'options' => array(array('label' => 'color', 'rank' => 3))));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder = $this->initQueryBuilderUpdater();
        $filterQueryBuilder->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$and": [ { "name": "dude" }, { "$and": [ { "options.label": "color" }, { "options.rank": 3 } ] } ] });',
            $this->toBson($mongoQB->getQuery())
        );

        // doctrine query builder without any joins and values for embedded field only
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, array(
            'doctrine_builder' => 'mongo',
        ));
        $form->submit(array('options' => array(array('label' => 'color', 'rank' => 3))));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder = $this->initQueryBuilderUpdater();
        $filterQueryBuilder->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$and": [ { "$and": [ { "options.label": "color" }, { "options.rank": 3 } ] } ] });',
            $this->toBson($mongoQB->getQuery())
        );

        // pre-fill parts
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, array(
            'doctrine_builder' => 'mongo',
        ));
        $form->submit(array('name' => 'dude', 'options' => array(array('label' => 'size', 'rank' => 5))));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder = $this->initQueryBuilderUpdater();
        $filterQueryBuilder->setParts(array('options' => 'options'));
        $filterQueryBuilder->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$and": [ { "name": "dude" }, { "$and": [ { "options.label": "size" }, { "options.rank": 5 } ] } ] });',
            $this->toBson($mongoQB->getQuery())
        );
    }

    public function testCustomConditionBuilder()
    {
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        // doctrine query builder without any joins + custom condition builder
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, array(
            'doctrine_builder'         => 'mongo',
            'filter_condition_builder' => function (ConditionBuilderInterface $builder) {
                $builder
                    ->root('or')
                        ->field('options.label')
                        ->andX()
                            ->field('options.rank')
                            ->field('name')
                        ->end()
                    ->end()
                ;
            },
        ));
        $form->submit(array('name' => 'dude', 'options' => array(array('label' => 'color', 'rank' => 6))));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$or": [ { "options.label": "color" }, { "$and": [ { "options.rank": 6 }, { "name": "dude" } ] } ] });',
            $this->toBson($mongoQB->getQuery())
        );

        // doctrine query builder without any joins + custom condition builder
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, array(
            'doctrine_builder'         => 'mongo',
            'filter_condition_builder' => function (ConditionBuilderInterface $builder) {
                $builder
                    ->root('and')
                        ->orX()
                            ->field('name')
                            ->field('options.label')
                        ->end()
                        ->orX()
                            ->field('options.rank')
                            ->field('position')
                        ->end()
                    ->end()
                ;
            },
        ));
        $form->submit(array('name' => 'dude', 'position' => 1, 'options' => array(array('label' => 'color', 'rank' => 6))));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$and": [ { "$or": [ { "name": "dude" }, { "options.label": "color" } ] }, { "$or": [ { "options.rank": 6 }, { "position": 1 } ] } ] });',
            $this->toBson($mongoQB->getQuery())
        );
    }

    public function testWithDataClass()
    {
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, array(
            'data_class'       => 'Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Document\Item',
            'doctrine_builder' => 'mongo',
        ));
        $form->submit(array('name' => 'dude', 'options' => array(array('label' => 'color', 'rank' => 6))));

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            'db.items.find({ "$and": [ { "name": "dude" }, { "$and": [ { "options.label": "color" }, { "options.rank": 6 } ] } ] });',
            $this->toBson($mongoQB->getQuery())
        );
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
