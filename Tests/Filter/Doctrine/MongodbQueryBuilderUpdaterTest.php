<?php

namespace Lexik\Bundle\FormFilterBundle\Tests\Filter\Doctrine;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Query\Builder;
use Lexik\Bundle\FormFilterBundle\Filter\Condition\ConditionBuilderInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterOperands;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\BooleanFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Document\Item;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\FormType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemCallbackFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemEmbeddedOptionsFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\ItemFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\Fixtures\Filter\RangeFilterType;
use Lexik\Bundle\FormFilterBundle\Tests\TestCase;

/**
 * Mongodb query builder tests.
 */
class MongodbQueryBuilderUpdaterTest extends TestCase
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    public function setUp(): void
    {
        parent::setUp();

        $this->dm = $this->getMongodbDocumentManager();
    }

    public function testBuildQuery()
    {
        $year = '2019';

        $bson = ['{}', '{"$and":[{"name":"blabla"}]}', '{"$and":[{"name":"blabla"},{"position":{"$gt":2}}]}', '{"$and":[{"name":"blabla"},{"position":{"$gt":2}},{"enabled":true}]}', '{"$and":[{"name":"blabla"},{"position":{"$gt":2}},{"enabled":true}]}', [
            '{"$and":[{"name":{"regex":".*blabla$","flags":"i"}},{"position":{"$lte":2}},{"createdAt":{"$date":{"$numberLong":"1569535200000"}}}]}',
            '{"$and":[{"name":"\/.*blabla$\/i"},{"position":{"$lte":2}},{"createdAt":{"$date":{"$numberLong":"1569535200000"}}}]}'
        ], [
            '{"$and":[{"name":{"regex":".*blabla$","flags":"i"}},{"position":{"$lte":2}},{"createdAt":{"$date":{"$numberLong":"1569583260000"}}}]}',
            '{"$and":[{"name":"\/.*blabla$\/i"},{"position":{"$lte":2}},{"createdAt":{"$date":{"$numberLong":"1569583260000"}}}]}'
        ]];

        $form = $this->formFactory->create(ItemFilterType::class);
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        // without binding the form
        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertMatchesRegularExpression($bson[0], $this->toBson($mongoQB->getQueryArray()));

        // bind a request to the form - 1 params
        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(['name' => 'blabla', 'position' => '']);

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[1], $this->toBson($mongoQB->getQueryArray()));

        // bind a request to the form - 2 params
        $form = $this->formFactory->create(ItemFilterType::class);

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(['name' => 'blabla', 'position' => 2]);

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[2], $this->toBson($mongoQB->getQueryArray()));

        // bind a request to the form - 3 params
        $form = $this->formFactory->create(ItemFilterType::class);

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(['name' => 'blabla', 'position' => 2, 'enabled' => BooleanFilterType::VALUE_YES]);

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[3], $this->toBson($mongoQB->getQueryArray()));

        // bind a request to the form - 3 params (use checkbox for enabled field)
        $form = $this->formFactory->create(ItemFilterType::class, null, ['checkbox' => true]);

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(['name' => 'blabla', 'position' => 2, 'enabled' => 'yes']);

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertEquals($bson[4], $this->toBson($mongoQB->getQueryArray()));

        // bind a request to the form - date + pattern selector
        $form = $this->formFactory->create(ItemFilterType::class, null, ['with_selector' => true]);

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(['name' => ['text' => 'blabla', 'condition_pattern' => FilterOperands::STRING_ENDS], 'position' => ['text' => 2, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL], 'createdAt' => ['year' => $year, 'month' => 9, 'day' => 27]]);

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertContains($this->toBson($mongoQB->getQueryArray()), $bson[5]);

        // bind a request to the form - datetime + pattern selector
        $form = $this->formFactory->create(ItemFilterType::class, null, ['with_selector' => true, 'datetime' => true]);

        $mongoQB = $this->createDoctrineQueryBuilder();
        $form->submit(['name' => ['text' => 'blabla', 'condition_pattern' => FilterOperands::STRING_ENDS], 'position' => ['text' => 2, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL], 'createdAt' => ['date' => ['year' => $year, 'month' => 9, 'day' => 27], 'time' => ['hour' => 13, 'minute' => 21]]]);

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);
        $this->assertContains($this->toBson($mongoQB->getQueryArray()), $bson[6]);
    }

    public function testDisabledFieldQuery()
    {
        $form = $this->formFactory->create(ItemFilterType::class, null, ['with_selector' => false, 'disabled_name' => true]);
        $form->submit(['name' => 'blabla', 'position' => 2]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"position":{"$gt":2}}]}',
            $this->toBson($mongoQB->getQueryArray())
        );
    }

    public function testApplyFilterOption()
    {
        $form = $this->formFactory->create(ItemCallbackFilterType::class);
        $form->submit(['name' => 'blabla', 'position' => 2]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"name":{"$ne":"blabla"}},{"position":{"$ne":2}}]}',
            $this->toBson($mongoQB->getQueryArray())
        );
    }

    public function testNumberRange()
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(['position' => ['left_number' => 1, 'right_number' => 3]]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"position":{"$gt":1,"$lt":3}}]}',
            $this->toBson($mongoQB->getQueryArray())
        );
    }

    public function testNumberRangeWithSelector()
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(['position_selector' => ['left_number' => ['text' => 4, 'condition_operator' => FilterOperands::OPERATOR_GREATER_THAN], 'right_number' => ['text' => 8, 'condition_operator' => FilterOperands::OPERATOR_LOWER_THAN_EQUAL]]]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"position_selector":{"$gt":4,"$lte":8}}]}',
            $this->toBson($mongoQB->getQueryArray())
        );
    }

    public function testNumberRangeDefaultValues()
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(['default_position' => ['left_number' => 1, 'right_number' => 3]]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"default_position":{"$gte":1,"$lte":3}}]}',
            $this->toBson($mongoQB->getQueryArray())
        );
    }

    public function testDateRange()
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(['createdAt' => ['left_date' => '2012-05-12', 'right_date' => ['year' => '2012', 'month' => '5', 'day' => '22']]]);


        $leftTimestamp = (new \DateTime('2012-05-12'))->getTimestamp() * 1000;
        $rightTimestamp = (new \DateTime('2012-05-22'))->getTimestamp() * 1000;

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"createdAt":{"$gte":{"$date":{"$numberLong":"' . $leftTimestamp . '"}},"$lt":{"$date":{"$numberLong":"' . $rightTimestamp . '"}}}}]}',
            $this->toBson($mongoQB->getQueryArray())
        );
    }

    public function testDateTimeRange()
    {
        // use filter type options
        $form = $this->formFactory->create(RangeFilterType::class);
        $form->submit(['updatedAt' => ['left_datetime' => ['date' => '2012-05-12', 'time' => '14:55'], 'right_datetime' => ['date' => ['year' => '2012', 'month' => '6', 'day' => '10'], 'time' => ['hour' => 22, 'minute' => 12]]]]);

        $leftTimestamp = (new \DateTime('2012-05-12 14:55:00'))->getTimestamp() * 1000;
        $rightTimestamp = (new \DateTime('2012-06-10 22:12:00'))->getTimestamp() * 1000;

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"updatedAt":{"$gte":{"$date":{"$numberLong":"' . $leftTimestamp . '"}},"$lt":{"$date":{"$numberLong":"' . $rightTimestamp . '"}}}}]}',
            $this->toBson($mongoQB->getQueryArray())
        );
    }

    public function testFilterStandardType()
    {
        $form = $this->formFactory->create(FormType::class);
        $form->submit(['name' => 'hey dude', 'position' => 99]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertContains(
            $this->toBson($mongoQB->getQueryArray()),
            [
                '{"$and":[{"name":{"regex":".*hey dude.*","flags":"i"}},{"position":99}]}',
                '{"$and":[{"name":"\/.*hey dude.*\/i"},{"position":99}]}'
            ]
        );
    }

    public function testEmbedFormFilter()
    {
        // doctrine query builder without any joins
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, ['doctrine_builder' => 'mongo']);
        $form->submit(['name' => 'dude', 'options' => [['label' => 'color', 'rank' => 3]]]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder = $this->initQueryBuilderUpdater();
        $filterQueryBuilder->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"name":"dude"},{"$and":[{"options.label":"color"},{"options.rank":3}]}]}',
            $this->toBson($mongoQB->getQueryArray())
        );

        // doctrine query builder without any joins and values for embedded field only
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, ['doctrine_builder' => 'mongo']);
        $form->submit(['options' => [['label' => 'color', 'rank' => 3]]]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder = $this->initQueryBuilderUpdater();
        $filterQueryBuilder->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"$and":[{"options.label":"color"},{"options.rank":3}]}]}',
            $this->toBson($mongoQB->getQueryArray())
        );

        // pre-fill parts
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, ['doctrine_builder' => 'mongo']);
        $form->submit(['name' => 'dude', 'options' => [['label' => 'size', 'rank' => 5]]]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder = $this->initQueryBuilderUpdater();
        $filterQueryBuilder->setParts(['options' => 'options']);
        $filterQueryBuilder->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"name":"dude"},{"$and":[{"options.label":"size"},{"options.rank":5}]}]}',
            $this->toBson($mongoQB->getQueryArray())
        );
    }

    public function testCustomConditionBuilder()
    {
        $filterQueryBuilder = $this->initQueryBuilderUpdater();

        // doctrine query builder without any joins + custom condition builder
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, ['doctrine_builder' => 'mongo', 'filter_condition_builder' => function (ConditionBuilderInterface $builder) {
            $builder
                ->root('or')
                    ->field('options.label')
                    ->andX()
                        ->field('options.rank')
                        ->field('name')
                    ->end()
                ->end()
            ;
        }]);
        $form->submit(['name' => 'dude', 'options' => [['label' => 'color', 'rank' => 6]]]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$or":[{"options.label":"color"},{"$and":[{"options.rank":6},{"name":"dude"}]}]}',
            $this->toBson($mongoQB->getQueryArray())
        );

        // doctrine query builder without any joins + custom condition builder
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, ['doctrine_builder' => 'mongo', 'filter_condition_builder' => function (ConditionBuilderInterface $builder) {
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
        }]);
        $form->submit(['name' => 'dude', 'position' => 1, 'options' => [['label' => 'color', 'rank' => 6]]]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $filterQueryBuilder->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"$or":[{"name":"dude"},{"options.label":"color"}]},{"$or":[{"options.rank":6},{"position":1}]}]}',
            $this->toBson($mongoQB->getQueryArray())
        );
    }

    public function testWithDataClass()
    {
        $form = $this->formFactory->create(ItemEmbeddedOptionsFilterType::class, null, ['data_class' => Item::class, 'doctrine_builder' => 'mongo']);
        $form->submit(['name' => 'dude', 'options' => [['label' => 'color', 'rank' => 6]]]);

        $mongoQB = $this->createDoctrineQueryBuilder();

        $this->initQueryBuilderUpdater()->addFilterConditions($form, $mongoQB);

        $this->assertEquals(
            '{"$and":[{"name":"dude"},{"$and":[{"options.label":"color"},{"options.rank":6}]}]}',
            $this->toBson($mongoQB->getQueryArray())
        );
    }

    protected function createDoctrineQueryBuilder(): Builder
    {
        return $this->dm
            ->getRepository(Item::class)
            ->createQueryBuilder();
    }

    protected function toBson(array $query)
    {
        return json_encode($query);
    }
}
