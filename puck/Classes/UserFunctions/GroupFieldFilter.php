<?php

namespace UBOS\Puck\UserFunctions;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GroupFieldFilter
{
    public function defaultLanguageOnly(array $parameters, $parentObject)
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $fieldValues = $parameters['values'];
        foreach($fieldValues as $key=>$val) {
            $valArr = explode('_', $val);
            if (count($valArr) < 2) {
                continue;
            }
            $table = $valArr[0];
            $uid = $valArr[1];
            $queryBuilder = $connectionPool->getQueryBuilderForTable($table);
            $result = $queryBuilder
                ->select('uid', 'sys_language_uid')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid))
                )
                ->executeQuery();
            $row = $result->fetchAssociative();
            if ($row['sys_language_uid'] > 0) {
                unset($fieldValues[$key]);
            }
        }
        return $fieldValues;
    }

}