<?php

namespace UBOS\Puck\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;

class PersistedAliasMapperOfCommaList extends PersistedAliasMapper
{
    const ROUTE_LIST_SEPARATOR = "--";

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
}