<?php
namespace UBOS\Puck\Hooks\WizardItems;

use TYPO3\CMS\Backend\Wizard\NewContentElementWizardHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;


class AddPresets implements NewContentElementWizardHookInterface
{
    /**
     *
     * @param array $wizardItems array of Wizard Items
     * @param \TYPO3\CMS\Backend\Controller\ContentElement\NewContentElementController $parentObject New Content element wizard
     *
     * @return    void
     */
    public function manipulateWizardItems(&$wizardItems, &$parentObject): void
    {
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $queryBuilderPages = $connectionPool->getQueryBuilderForTable('pages');
        $queryBuilderPages->getRestrictions()->removeAll();
        $presetFolders = $queryBuilderPages
            ->select('uid','doktype','module')
            ->from('pages')
            ->where(
                $queryBuilderPages->expr()->eq('doktype', $queryBuilderPages->createNamedParameter(254)),
                $queryBuilderPages->expr()->eq('module', $queryBuilderPages->createNamedParameter('presets'))
            )
            ->execute()
            ->fetchAll();
        if (empty($presetFolders)) {
            return;
        }
        $presetFolderIds = '';
        foreach($presetFolders as $presetFolder) {
            $presetFolderIds .= $presetFolder['uid'].',';
        }
        $queryBuilderContent = $connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilderContent->getRestrictions()->removeAll();
        $contentElements = $queryBuilderContent
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilderContent->expr()->in('pid', $queryBuilderContent->createNamedParameter($presetFolderIds)),
                $queryBuilderContent->expr()->eq('hidden', $queryBuilderContent->createNamedParameter(0)),
                $queryBuilderContent->expr()->eq('deleted', $queryBuilderContent->createNamedParameter(0))
            )
            ->execute()
            ->fetchAll();
        $excludeColumns = 'deleted,colPos,l10n_source,l10n_state,tx_impexp_origuid,t3_origuid,l18n_diffsource,t3ver_oid,t3ver_wsid,t3ver_state,t3ver_stage,l18n_parent,sys_language_uid,uid,pid,rowDescription,tstamp,crdate,cruser_id,starttime,endtime,sorting,hidden,tx_container_parent';

        $newItems = [];
        $newItems['00_presets'] = [
            'header' => 'Presets',
        ];

        foreach($contentElements as $index => $row) {
            $elementKey = 'preset_'.$row['CType'].'_'.$row['uid'];

            $modelNameLowercaseUnderscored = str_replace('puck_','',$row['CType']);

            $newItems['00_presets_'.$elementKey] = [
                'iconIdentifier' => $modelNameLowercaseUnderscored,
                'title' => 'Preset: '.$row['header'],
                'description' => 'Custom preset for '.LocalizationUtility::translate('LLL:EXT:puck/Resources/Private/Language/locallang_be.xlf:wizard.'.$modelNameLowercaseUnderscored).' element.',
            ];

            foreach($row as $col => $val) {
                if($val === '' || $val === null || GeneralUtility::inList($excludeColumns, $col)) {
                    continue;
                }
                $newItems['00_presets_'.$elementKey]['tt_content_defValues'][$col] = $val;
            }
        }
        $wizardItems = $newItems + $wizardItems;
    }
}