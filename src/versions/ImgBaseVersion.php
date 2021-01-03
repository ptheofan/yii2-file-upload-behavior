<?php
namespace ptheofan\behaviors\file\versions;

use RuntimeException;
use ptheofan\helpers\ImageHelper;

/**
 * Save the upload as an image in the original dimensions
 */
class ImgBaseVersion extends BaseVersion
{
    public string $name = 'JPG Base Version';
    public string $format;
    public string $ext;

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getExt(): string
    {
        return $this->ext;
    }

    /**
     * Always add the ext if missing
     *
     * @return string|null
     */
    public function getFilename(): ?string
    {
        $basename = $this->fileAttribute->getBasename();
        if (!$basename) {
            return null;
        }

        $ext = pathinfo($basename, PATHINFO_EXTENSION);
        if ($ext !== $this->ext) {
            $basename .= sprintf('.%s', $this->getExt());
        }

        return $basename;
    }

    public function create(): void
    {
        $upload = $this->fileAttribute->getUpload();

        // Ensure png format
        $img = ImageHelper::makeInstance($upload->getContents());
        $pngContents = $img->get($this->getFormat());

        $this->fileAttribute->getStorageManager()->storeString($pngContents, $this->getStorageLocation());
    }
}
