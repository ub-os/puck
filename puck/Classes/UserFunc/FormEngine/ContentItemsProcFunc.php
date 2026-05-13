<?php

namespace UBOS\Puck\UserFunc\FormEngine;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use UBOS\Puck\Domain\Repository\PageRepository;

/**
 * ItemsProcFunc methods for 'tt_content' fields
 */
class ContentItemsProcFunc
{
	use ItemsProcFuncUtilsTrait;

	/**
	 * @var array|array[]
	 */
	protected array $keepItemsMap = [
		'puck_cover_media' => [
			'media_layout' => 'above,below,left,right'
		],
		'puck_modal' => [
			'media_layout' => 'above,below,left,right'
		],
		'puck_card' => [
			'media_layout' => 'above,below,left,right'
		],
		'puck_page_menu' => [
			'media_layout' => 'above,below,left,right'
		],
	];

	protected function getContainerParent(array $params): ?array
	{
		return BackendUtility::getRecord('tt_content', $this->val($params['row']['tx_container_parent']), '*', '', true) ?? null;
	}

	protected function getMaxWidth(array $params): int
	{
		if (!$this->val($params['row']['tx_container_parent'])) {
			return $this->val($params['row']['container_width']);
		}
		$parentRow = $this->getContainerParent($params);
		if (!$parentRow) {
			return $this->val($params['row']['container_width']);
		}
		return $this->val($parentRow['container_width']);
	}

	/**
	 * @param $params
	 * @return void
	 */
	public function keepItems(&$params): void
	{
		if (!isset($params['row']) || !$params['row'] || !$params['row']['uid']) return;
		$keepItems = $this->keepItemsMap[$this->val($params['row']['CType'])][$params['field']];
		if ($keepItems) {
			$params['items'] = $this->filterItemsByValues($params['items'], $keepItems);
		}
	}

	/**
	 * @param $params
	 * @return void
	 */
	public function containerWidth(&$params): void
	{
		if (!isset($params['row']) || !$params['row'] || !$params['row']['uid']) return;
		if (!$this->val($params['row']['tx_container_parent'])) {
			$params['items'] = array_filter($params['items'], function ($item) {
				return $item[1] >= 4;
			});
			return;
		}
		$parentRow = $this->getContainerParent($params);
		// only allow offset values that are smaller than half of (12 - container width)
		$params['items'] = array_filter($params['items'], function ($item) use ($parentRow) {
			return $item[1] <= $parentRow['container_width'];
		});
	}


	/**
	 * @param $params
	 * @return void
	 */
	public function containerOffset(&$params): void
	{
		if (!isset($params['row']) || !$params['row'] || !$params['row']['uid']) return;
		// only allow offset values that are smaller than half of (12 - container width)
		$items = array_filter($params['items'], function ($item) use ($params) {
			return $item[1] <= ((12 - $this->val($params['row']['container_width'])) / 2);
		});
		$params['items'] = $items;
	}

	/**
	 * @param $params
	 * @return void
	 */
	public function mediaLayout(&$params): void
	{
		if (!isset($params['row']) || !$params['row'] || !$params['row']['uid']) return;
		$CType = $this->val($params['row']['CType']);
		if ($CType != 'puck_page_menu') {
			return;
		}
		$allowedValues = 'above,below,left,right';
		$itemWidth = $this->val($params['row']['item_column_width']);
		if ($itemWidth < 4) {
			$allowedValues = 'above,below';
		}
		$params['items'] = $this->filterItemsByValues($params['items'], $allowedValues);
	}

	/**
	 * @param $params
	 * @return void
	 */
	public function itemColumnWidth(&$params): void
	{
		if (!isset($params['row']) || !$params['row'] || !$params['row']['uid']) return;
		// failsafe for child content elements
		$containerWidth = $this->val($params['row']['container_width']);
		$maxWidth = $this->val($this->getMaxWidth($params));
		if ($containerWidth > $maxWidth) {
			$containerWidth = $maxWidth;
		}

		$CType = $this->val($params['row']['CType']);
		// maximum width = media element with media_layout beside/float ? media width : container width
		if (in_array($CType, ['puck_media', 'puck_media_column', 'puck_accordion']) && in_array($this->val($params['row']['media_layout']), ['left', 'right', 'left-float', 'right-float'])) {
			$maximum = $this->val($params['row']['media_column_width']);
		} else {
			$maximum = $containerWidth;
		}
		$items = array_filter($params['items'], function ($item) use ($params, $maximum) {
			return $item[1] <= $maximum;
		});
		$params['items'] = $items;
	}

	/**
	 * @param $params
	 * @return void
	 */
	public function textColumnWidth(&$params): void
	{
		if (!isset($params['row']) || !$params['row'] || !$params['row']['uid']) return;
		$CType = $this->val($params['row']['CType']);
		$mediaLayout = $this->val($params['row']['media_layout']);

		// failsafe for child content elements
		$containerWidth = $this->val($params['row']['container_width']);
		$maxWidth = $this->val($this->getMaxWidth($params));
		if ($containerWidth > $maxWidth) {
			$containerWidth = $maxWidth;
		}
		if ($CType == 'puck_page_menu') {
			$containerWidth = $this->val($params['row']['item_column_width']);
		}
		// maximum width = container width - media width
		if (in_array($CType, ['puck_media', 'puck_page_menu', 'puck_media_column', 'puck_accordion']) && in_array($mediaLayout, ['left-float', 'right-float', 'above', 'below'])) {
			$params['items'] = array_filter($params['items'], function ($item) use ($containerWidth) {
				return $item[1] <= $containerWidth;
			});
			return;
		}
		$maximum = $containerWidth - $this->val($params['row']['media_column_width']);
		$items = array_filter($params['items'], function ($item) use ($params, $maximum) {
			return $item[1] <= $maximum;
		});
		$params['items'] = $items;
	}

	/**
	 * @param $params
	 * @return void
	 */
	public function mediaColumnWidth(&$params): void
	{
		if (!isset($params['row']) || !$params['row'] || !$params['row']['uid']) return;
		$CType = $this->val($params['row']['CType']);
		// maximum width = container width - media width
		if (!in_array($CType, ['puck_media', 'puck_modal', 'puck_media_column', 'puck_page_menu', 'puck_card', 'puck_accordion'])) {
			return;
		}
		$mediaLayout = $this->val($params['row']['media_layout']);

		// failsafe for child content elements
		$containerWidth = $this->val($params['row']['container_width']);
		$maxWidth = $this->val($this->getMaxWidth($params));
		if ($containerWidth > $maxWidth) {
			$containerWidth = $maxWidth;
		}

		if ($CType == 'puck_page_menu') {
			$containerWidth = $this->val($params['row']['item_column_width']);
		}
		if (in_array($mediaLayout, ['above', 'below'])) {
			$params['items'] = array_filter($params['items'], function ($item) use ($containerWidth) {
				return $item[1] == $containerWidth;
			});
			return;
		}
		if (in_array($mediaLayout, ['left-float', 'right-float']) || (in_array($CType, ['puck_modal', 'puck_card']) && in_array($mediaLayout, ['left', 'right']))) {
			// if media_layout is float: maximum width = container width - 2
			$maximum = $containerWidth - 2;
		} else {
			// maximum width = container width - text width
			$maximum = $containerWidth - $this->val($params['row']['text_column_width']);
		}
		$items = array_filter($params['items'], function ($item) use ($params, $maximum) {
			return $item[1] <= $maximum;
		});
		$params['items'] = $items;
	}
}
