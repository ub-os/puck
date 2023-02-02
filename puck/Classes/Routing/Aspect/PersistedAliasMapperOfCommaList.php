<?php

namespace UBOS\Puck\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\PersistedAliasMapper;

class PersistedAliasMapperOfCommaList extends PersistedAliasMapper
{
    /**
     * {@inheritdoc}
     */
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
            implode('--',$results)
        );
    }
    public function resolve(string $value): ?string
    {
        $results = [];
        $value = $this->routeValuePrefix . $this->purgeRouteValuePrefix($value);
        foreach(explode('--',$value) as $val) {
            $results[] = $this->findByRouteFieldValue($val);
        }
        foreach($results as $index=>$res) {
            if ($res[$this->languageParentFieldName] ?? null > 0) {
                $results[$index] = $res[$this->languageParentFieldName];
            }
            else if (isset($res['uid'])) {
                $results[$index] = $res['uid'];
            } else {
                $results[$index] = null;
            }
        }
        return implode(',',$results);
    }
}