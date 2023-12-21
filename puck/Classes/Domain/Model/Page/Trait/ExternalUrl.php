<?php
namespace UBOS\Puck\Domain\Model\Page\Trait;

trait ExternalUrl {
    public string $url = '';
    public function getLinkParameter(): string
    {
        return $this->url ?: $this->getUid();
    }
}