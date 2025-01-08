<?php

namespace UBOS\Puck\Menu;

use TYPO3\CMS\Extbase\Persistence\Generic\QueryResult;
use UBOS\Puck\Menu\Dto\MenuDemand;

interface MenuDemandRepository
{
	public function findByMenuDemand(MenuDemand $demand): QueryResult|array;
}