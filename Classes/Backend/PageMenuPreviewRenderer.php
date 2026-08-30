<?php

namespace UBOS\Puck\Backend;

use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Domain\FlexFormFieldValues;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewInterface;

/**
 * Preview renderer for the PageMenu plugin.
 */
class PageMenuPreviewRenderer extends BasicPreviewRenderer
{
	protected function assignPreviewContentVariables(ViewInterface $view, GridColumnItem $item): void
	{
		$flexFormValues = $item->getRecord()->get('pi_flexform');
		if (!$flexFormValues instanceof FlexFormFieldValues) {
			return;
		}
		$settings = [];
		foreach ($flexFormValues->toArray() as $sheet) {
			$settings = array_replace_recursive($settings, $sheet['settings'] ?? []);
		}
		$view->assign('pageMenuSettings', $settings);
		$view->assign('pageMenuProcessed', $this->getDataForPageMenuFlexFormPreview($settings));
	}

	protected function getDataForPageMenuFlexFormPreview(array $settings): array
	{
		$processedMenuData = [];
		$uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
		foreach (['records', 'parents'] as $key) {
			if (!empty($settings['demand'][$key])) {
				foreach (GeneralUtility::intExplode(',', (string)$settings['demand'][$key], true) as $uid) {
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
				'uids' => $settings['demand']['categoryGroups']['main']['uids'] ?? '',
			],
			'filter_categories' => [
				'table' => 'sys_category',
				'title' => 'Filter categories',
				'uids' => isset($settings['categoryFilter']) ? $settings['categoryFilter']['categories'] : '',
			],
			'filter_category_groups' => [
				'table' => 'sys_category',
				'title' => 'Filter category groups',
				'uids' => isset($settings['categoryFilter']) ? $settings['categoryFilter']['treeCategories'] : '',
			],
		];
		foreach ($recordGroups as $key => $recordGroup) {
			if (!empty($recordGroup['uids'])) {
				foreach (GeneralUtility::intExplode(',', (string)$recordGroup['uids'], true) as $uid) {
					$record = BackendUtility::getRecord($recordGroup['table'], $uid, '*', '', true);
					if (!$record) {
						continue;
					}
					$record['backend_link'] = $uriBuilder->buildUriFromRoute(
						'record_edit',
						[
							'edit' => [$recordGroup['table'] => [$uid => 'edit']],
							'returnUrl' => GeneralUtility::getIndpEnv('REQUEST_URI'),
						]
					);
					$record['table'] = $recordGroup['table'];
					$record['backend_link_title'] = 'Edit record';
					$processedMenuData[$key][] = $record;
				}
			}
		}
		return $processedMenuData;
	}

}
