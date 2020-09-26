<?php declare(strict_types=1);

namespace XBase\Writable;

/**
 * @property $filepath
 */
trait CloneTrait
{
    /** @var string */
    private $cloneFilepath;

    /**
     * We will perform any edits on clone.
     */
    private function clone(): void
    {
        $info = pathinfo($this->filepath);
        $this->cloneFilepath = "{$info['dirname']}/~{$info['basename']}";
        if (!copy($this->filepath, $this->cloneFilepath)) {
            throw new \RuntimeException('Failed to clone original file: '.$this->filepath);
        }
    }
}