<?php
namespace ptheofan\behaviors\file\versions;

use RuntimeException;
use ptheofan\helpers\ImageHelper;

/**
 * Save the upload as a PNG in the original dimensions
 */
class PngBaseVersion extends BaseVersion
{
    public string $name = 'PNG Base Version';

    /**
     * This is PNG file. Always add the .png ext if missing
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
        if ($ext !== 'png') {
            $basename .= '.png';
        }

        return $basename;
    }

    protected function readOrFail(string $file): string
    {
        $contents = file_get_contents($file);
        if ($contents === false) {
            throw new RuntimeException("Version {$this->getName()} could not read the contents of the file {$file}");
        }

        return $contents;
    }

    public function create(): void
    {
        $upload = $this->fileAttribute->getUpload();

        // Ensure png format
        $img = ImageHelper::makeInstance($upload->getContents());
        $pngContents = $img->get('png');

        $this->fileAttribute->getStorageManager()->storeString($pngContents, $this->getStorageLocation());
    }
}
