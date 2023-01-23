<?php

namespace UBOS\Puck\Hooks\WebLayoutHeader;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use UBOS\Puck\Domain\Model\Post;

use UBOS\Puck\Domain\Repository\PostRepository;

class PostHeader extends AbstractHeader
{
    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function getPropertyPermissions(): array
    {
        $class = Post::class;
        $dataMap = GeneralUtility::makeInstance(ObjectManager::class)->get(DataMapper::class)->getDataMap($class);
        $backendUserAuthentication = $this->getBackendUser();

        $permissions = [];
        foreach (GeneralUtility::makeInstance(\ReflectionClass::class, $class)->getProperties() ?? [] as $reflection) {
            if ($property = $reflection->name) {
                $permissions[$property] = ($columnMap = $dataMap->getColumnMap($property)) && ((($table = $columnMap->getChildTableName()) && $backendUserAuthentication->check('tables_select', $table)) || $backendUserAuthentication->check('non_exclude_fields', $dataMap->getTableName() . ':' . $columnMap->getColumnName()));
            }
        }

        return $permissions;
    }

    /** @throws AspectNotFoundException */
    public function render(): string
    {
        // Check if the page is a post
        if ((int)($this->row['doktype'] ?? 0) === Post::DOKTYPE) {
            // Get the page repository
            $pageRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(PostRepository::class);
            return $this->createView('EXT:puck/Resources/Private/Fluid/Backend/Templates/WebLayoutHeader/Post.html', [
                'post' => $pageRepository->findByUid($this->id),
                'row' => $this->row,
                'propertyPermissions' => $this->getPropertyPermissions()
            ])->render();
        }
        return '';
    }
}