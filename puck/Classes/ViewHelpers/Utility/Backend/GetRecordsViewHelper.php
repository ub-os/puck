<?php


namespace UBOS\Puck\ViewHelpers\Utility\Backend;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use UBOS\Puck\ViewHelpers\Backend\QueryBuilder;

class GetRecordsViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('table', 'string', '', true);
        $this->registerArgument('where', 'string', '', false, '');
        $this->registerArgument('uids', 'mixed', '', false, '');
        $this->registerArgument('sorting', 'string', '', false, '');
    }
    protected function getQueryBuilderForTable($table)
    {
      return GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
    }
    public function render(): array
    {
      $table = $this->arguments['table'];
      $sorting = $this->arguments['sorting'];
      $where = $this->arguments['where'];
      $uids = $this->arguments['uids'];
      $result = [];
      if ($uids) {
          if (gettype($uids) == 'string') {
              $uids = explode(',', $uids);
          }
          foreach ($uids as $uid) {
              $record = BackendUtility::getRecord($table, $uid, '*', '', true);
              $result[] = $record;
          }
          return $result;
      } else {
          $queryBuilder = $this->getQueryBuilderForTable($table);
          $queryBuilder->getRestrictions()
              ->removeAll()
              ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
          $res = $queryBuilder
              ->select('*')
              ->from($table)
              ->where(QueryHelper::stripLogicalOperatorPrefix($where))
              ->execute();
          $index = 0;
          while ($record = $res->fetch()) {
            $result[$index] = $record;
            $index++;
          }
          if ($sorting) {
            return ArrayUtility::sortArraysByKey($result, $sorting);
          } else {
            return $result;
          }
      }
    }
}

