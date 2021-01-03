<?php
namespace ptheofan\behaviors\file\versions;

use RuntimeException;
use ptheofan\helpers\ImageHelper;

/**
 * Generate a resized image
 */
class ImgResizedVersion extends ImgBaseVersion
{
    public string $name = 'JPG Resized Version';
    public ?string $prefix = null;
    public ?string $suffix = null;
    public ?int $width = null;
    public ?int $height = null;

    public function init(): void
    {
        if (!$this->prefix && !$this->suffix) {
            throw new RuntimeException("Version {$this->getName()} should have at least one of prefix or suffix set");
        }

        if (!$this->width && !$this->height) {
            throw new RuntimeException("Version {$this->getName()} should have at least one of width or height set");
        }
    }

    public function getFilename(): ?string
    {
        $filename = parent::getFilename();

        if ($this->prefix) {
            $filename = sprintf('%s%s', $this->prefix, $this->filename);
        }

        if ($this->suffix) {
            $basename = pathinfo($filename, PATHINFO_FILENAME);
            $filename = sprintf('%s%s.%s', $basename, $this->suffix, $this->ext);
        }

        return $filename;
    }

    public function create(): void
    {
        $upload = $this->fileAttribute->getUpload();

        // Ensure png format and resize
        $pngContents = ImageHelper::smartResize($upload->getContents(), $this->width, $this->height)
            ->get($this->ext);

        $this->fileAttribute->getStorageManager()->storeString($pngContents, $this->getStorageLocation());
    }
}
