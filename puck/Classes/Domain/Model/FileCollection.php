<?php

namespace UBOS\Puck\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Annotation\ORM\Lazy;
use TYPO3\CMS\Extbase\Annotation\ORM\Transient;
use TYPO3\CMS\Core\Resource\FileCollectionRepository;

use UBOS\Puckloader\Attribute\ModelColumn;
use UBOS\Puckloader\Attribute\ModelPersistence;

#[ModelPersistence("sys_file_collection")]
class FileCollection extends AbstractEntity
{
    /**
     * @var FileCollectionRepository|null
     */
    protected ?FileCollectionRepository $fileCollectionRepository;

    /**
     * @param FileCollectionRepository $fileCollectionRepository
     * @return void
     */
    public function injectFileCollectionRepository(FileCollectionRepository $fileCollectionRepository): void
    {
        $this->fileCollectionRepository = $fileCollectionRepository;
    }

    /**
     * @var string
     */
    public string $type = '';

    /**
     * @var array|null
     */
    protected ?array $files = null;

    /**
     * @return array|null
     */
    public function getFiles(): ?array
    {
        if ($this->files === null) {
            $collection = $this->fileCollectionRepository->findByUid($this->uid);
            $collection->loadContents();
            $this->files = $collection->getItems();
        }
        return $this->files;
    }

    /**
     * @param array|null $files
     */
    public function setFiles(?array $files): void
    {
        $this->files = $files;
    }

}