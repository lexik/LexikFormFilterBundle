<?php

namespace Lexik\Bundle\FormFilterBundle\Event\Subscriber;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\FormFilterBundle\Event\GetFilterConditionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Register listeners to compute conditions to be applied on a Doctrine ORM query builder.
 *
 * @author Cédric Girard <c.girard@lexik.fr>
 */
class DoctrineORMSubscriber extends AbstractDoctrineSubscriber implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Lexik form filter types
            'lexik_form_filter.apply.orm.filter_boolean' => ['filterBoolean'],
            'lexik_form_filter.apply.orm.filter_checkbox' => ['filterCheckbox'],
            'lexik_form_filter.apply.orm.filter_choice' => ['filterValue'],
            'lexik_form_filter.apply.orm.filter_date' => ['filterDate'],
            'lexik_form_filter.apply.orm.filter_date_range' => ['filterDateRange'],
            'lexik_form_filter.apply.orm.filter_datetime' => ['filterDateTime'],
            'lexik_form_filter.apply.orm.filter_datetime_range' => ['filterDateTimeRange'],
            'lexik_form_filter.apply.orm.filter_entity' => ['filterEntity'],
            'lexik_form_filter.apply.orm.filter_number' => ['filterNumber'],
            'lexik_form_filter.apply.orm.filter_number_range' => ['filterNumberRange'],
            'lexik_form_filter.apply.orm.filter_text' => ['filterText'],
            // Symfony types
            'lexik_form_filter.apply.orm.text' => ['filterText'],
            'lexik_form_filter.apply.orm.email' => ['filterValue'],
            'lexik_form_filter.apply.orm.integer' => ['filterValue'],
            'lexik_form_filter.apply.orm.money' => ['filterValue'],
            'lexik_form_filter.apply.orm.number' => ['filterValue'],
            'lexik_form_filter.apply.orm.percent' => ['filterValue'],
            'lexik_form_filter.apply.orm.search' => ['filterValue'],
            'lexik_form_filter.apply.orm.url' => ['filterValue'],
            'lexik_form_filter.apply.orm.choice' => ['filterValue'],
            'lexik_form_filter.apply.orm.entity' => ['filterEntity'],
            'lexik_form_filter.apply.orm.country' => ['filterValue'],
            'lexik_form_filter.apply.orm.language' => ['filterValue'],
            'lexik_form_filter.apply.orm.locale' => ['filterValue'],
            'lexik_form_filter.apply.orm.timezone' => ['filterValue'],
            'lexik_form_filter.apply.orm.date' => ['filterDate'],
            'lexik_form_filter.apply.orm.datetime' => ['filterDate'],
            'lexik_form_filter.apply.orm.birthday' => ['filterDate'],
            'lexik_form_filter.apply.orm.checkbox' => ['filterValue'],
            'lexik_form_filter.apply.orm.radio' => ['filterValue'],
        ];
    }

    /**
     * @param GetFilterConditionEvent $event
     * @throws \Exception
     */
    public function filterEntity(GetFilterConditionEvent $event)
    {
        $expr = $event->getFilterQuery()->getExpr();
        $values = $event->getValues();

        if (is_object($values['value'])) {
            $paramName = $this->generateParameterName($event->getField());
            $filterField = $event->getField();

            /** @var QueryBuilder $queryBuilder */
            $queryBuilder = $event->getQueryBuilder();

            if ($dqlFrom = $event->getQueryBuilder()->getDQLPart('from')) {
                $rootPart = reset($dqlFrom);
                $fieldName = preg_replace('/^'.$rootPart->getAlias().'\./', '', $event->getField());
                $metadata = $queryBuilder->getEntityManager()->getClassMetadata($rootPart->getFrom());

                if (isset($metadata->associationMappings[$fieldName]) && (!$metadata->associationMappings[$fieldName]['isOwningSide'] || $metadata->associationMappings[$fieldName]['type'] === ClassMetadataInfo::MANY_TO_MANY)) {
                    if (!$event->getFilterQuery()->hasJoinAlias($fieldName)) {
                        $queryBuilder->leftJoin($event->getField(), $fieldName);
                    }

                    $filterField = $fieldName;
                }
            }

            if ($values['value'] instanceof Collection) {
                $ids = [];

                foreach ($values['value'] as $value) {
                    $ids[] = $this->getEntityIdentifier($value, $queryBuilder->getEntityManager());
                }

                if (count($ids) > 0) {
                    $event->setCondition(
                        $expr->in($filterField, ':' . $paramName),
                        [$paramName => [$ids, Connection::PARAM_INT_ARRAY]]
                    );
                }
            } else {
                $event->setCondition(
                    $expr->eq($filterField, ':' . $paramName),
                    [$paramName => [$this->getEntityIdentifier($values['value'], $queryBuilder->getEntityManager()), Types::INTEGER]]
                );
            }
        }
    }

    /**
     * @param object $value
     * @return integer
     * @throws \RuntimeException
     */
    protected function getEntityIdentifier($value, EntityManagerInterface $em)
    {
        $class = get_class($value);
        $metadata = $em->getClassMetadata($class);

        if ($metadata->isIdentifierComposite) {
            throw new \RuntimeException(sprintf('Composite identifier is not supported by FilterEntityType.', $class));
        }

        $identifierValues = $metadata->getIdentifierValues($value);

        if (empty($identifierValues)) {
            throw new \RuntimeException(sprintf('Can\'t get identifier value for class "%s".', $class));
        }

        return array_shift($identifierValues);
    }
}
