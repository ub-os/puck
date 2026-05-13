<?php

namespace UBOS\Puck\Backend;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Preview renderer for the PageMenu plugin.
 */
class PageMenuPreviewRenderer extends BasicPreviewRenderer
{
	public function renderPageModulePreviewContent(GridColumnItem $item): string
	{
		$record = $item->getRecord();
		$flexFormValues = $record->get('pi_flexform');
		$sheets = $flexFormValues->getSheets();
		$settings = array_merge_recursive(...array_values($sheets))['settings'] ?? [];
		$this->view->assign('pageMenuSettings', $settings);
		$this->view->assign('pageMenuProcessed', $this->getDataForPageMenuFlexFormPreview($settings));
		$item->setRecord($record);
		return parent::renderPageModulePreviewContent($item);
	}

	protected function getDataForPageMenuFlexFormPreview(array $settings): array
	{
		$processedMenuData = [];
		$uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
		foreach (['records', 'parents'] as $key) {
			if (!empty($settings['demand'][$key])) {
				$uids = explode(',', $settings['demand'][$key]);
				foreach ($uids as $uid) {
					$page = BackendUtility::getRecord('pages', $uid, '*', '', true);
					if (!$page) {
						continue;
					}
					$page['backend_link'] = $uriBuilder->buildUriFromRoute(
						'web_layout',
						['id' => $page['uid']]
					);
					$page['table'] = 'pages';
					$page['backend_link_title'] = 'Switch to this page';
					$processedMenuData[$key][] = $page;
				}
			}
		}
		$doktypes = ['row' => ['uid' => 1], 'items' => $GLOBALS['TCA']['pages']['columns']['doktype']['config']['items']];
		$processedMenuData['doktypeIcons'] = [];
		foreach ($doktypes['items'] as $item) {
			if (in_array($item['value'], $settings['demand']['additionalSettings']['types'] ?? [])) {
				$processedMenuData['doktypeIcons'][$item['label']] = $item['icon'];
			}
		}
		$recordGroups = [
			'categories' => [
				'table' => 'sys_category',
				'title' => 'Categories',
				'titleField' => 'title',
				'uids' => $settings['demand']['categoryGroups']['main']['uids'] ?? ''
			],
			'filter_categories' => [
				'table' => 'sys_category',
				'title' => 'Filter categories',
				'titleField' => 'title',
				'uids' => isset($settings['categoryFilter']) ? $settings['categoryFilter']['categories'] : ''
			],
			'filter_category_groups' => [
				'table' => 'sys_category',
				'title' => 'Filter category groups',
				'titleField' => 'title',
				'uids' => isset($settings['categoryFilter']) ? $settings['categoryFilter']['treeCategories'] : ''
			],
		];
		foreach ($recordGroups as $key => $recordGroup) {
			if (isset($recordGroup['uids']) && !empty($recordGroup['uids'])) {
				$uids = explode(',', $recordGroup['uids']);
				foreach ($uids as $uid) {
					if (!$uid) {
						continue;
					}
					$record = BackendUtility::getRecord($recordGroup['table'], $uid, '*', '', true);
					$record['backend_link'] = $uriBuilder->buildUriFromRoute(
						'record_edit',
						[
							'edit' => [$recordGroup['table'] => [$uid => 'edit']],
							'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
						]
					);
					$record['table'] = $recordGroup['table'];
					if ($recordGroup['titleField'] !== 'title' && isset($record[$recordGroup['titleField']])) {
						$record['title'] = $record[$recordGroup['titleField']];
					}
					$record['backend_link_title'] = 'Edit record';
					$processedMenuData[$key][] = $record;
				}
			}
		}
		return $processedMenuData;
	}

}