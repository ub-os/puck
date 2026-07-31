<?php

namespace UBOS\Puck\Configuration;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;

final readonly class FlexFormModifier
{
	#[AsEventListener]
	public function modifyDataStructure(AfterFlexFormDataStructureParsedEvent $event): void
	{
		$identifier = $event->getIdentifier();
		// modify flexform data structure for puck_page_menu content element
		// which uses the flexform EXT:menu_controls/Configuration/FlexForms/PageMenu.xml
		if (($identifier['dataStructureKey'] ?? '') === 'puck_page_menu') {
			$structure = $event->getDataStructure();
			$structure['sheets']['selection']['ROOT']['el']['settings.pageTeasers'] = [
				'label' => 'Teasers',
				'description' => 'Teasers merely override displayed title, image etc. of the page they belong to. They will have no effect if their page is not in the selection for display.',
				'config' => [
					'type' => 'group',
					'allowed' => 'tx_puck_domain_model_page_teaser',
					'size' => 10,
					'maxitems' => 999,
					'minitems' => 0,
				]
			];
			$structure['sheets']['order']['ROOT']['el']['settings.demand.orderField']['config']['items'][] = [
				'label' => 'Post date',
				'value' => 'post_date'
			];
			 $event->setDataStructure($structure);
		}
	}
}