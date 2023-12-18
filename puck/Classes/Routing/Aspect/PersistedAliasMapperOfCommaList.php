<?php

namespace UBOS\Puck\Routing\Aspect;

use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;

class PersistedAliasMapperOfCommaList extends PersistedAliasMapper
{
    const ROUTE_LIST_SEPARATOR = ".";

    public function generate(string $value): ?string
    {
        $results = [];
        foreach(explode(',',$value) as $val) {
            $result = $this->resolveOverlay($this->findByIdentifier($val));
            if (isset($result[$this->routeFieldName])) {
                $results[] = $result[$this->routeFieldName];
            }
        }
        if (!$results) {
            return null;
        }
        return $this->purgeRouteValuePrefix(
            implode(self::ROUTE_LIST_SEPARATOR, $results)
        );
    }
    public function resolve(string $value): ?string
    {
        $results = [];
        $value = $this->routeValuePrefix . $this->purgeRouteValuePrefix($value);
        foreach(explode(self::ROUTE_LIST_SEPARATOR, $value) as $val) {
            $results[] = $this->findByRouteFieldValue($val);
        }
        foreach($results as $index=>$res) {
            if ($res[$this->languageParentFieldName] ?? null > 0) {
                $results[$index] = $res[$this->languageParentFieldName];
            }
            else if (isset($res['uid'])) {
                $results[$index] = $res['uid'];
            }
        }
        if (!$results[0]) {
            return null;
        }
        return implode(',',$results);
    }

    protected function findByIdentifier(string $value): ?array
    {
        $languageAware = $this->languageFieldName !== null && $this->languageParentFieldName !== null;
        $languageIds = $this->resolveAllRelevantLanguageIds();
        if (!MathUtility::canBeInterpretedAsInteger($value)) {
            return null;
        }
        $queryBuilder = $this->createQueryBuilder();
        if ($languageAware && $languageIds && $languageIds !== [-1,0]) {
            $where = [
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($value, Connection::PARAM_INT)),
                    $queryBuilder->expr()->eq(
                        $this->languageParentFieldName,
                        $queryBuilder->createNamedParameter($value, Connection::PARAM_INT)),
                ),
                $queryBuilder->expr()->in(
                    $this->languageFieldName,
                    $queryBuilder->createNamedParameter($this->resolveAllRelevantLanguageIds(), Connection::PARAM_INT_ARRAY)
                ),
            ];
        } else {
            $where = [
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($value, Connection::PARAM_INT)
                )
            ];
        }
        $result = $queryBuilder
            ->select(...$this->persistenceFieldNames)
            ->where(...$where)
            ->executeQuery()
            ->fetchAssociative();
        return $result !== false ? $result : null;
    }
}