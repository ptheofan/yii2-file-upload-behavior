<?php
namespace ptheofan\behaviors\file\versions;

use \ptheofan\behaviors\file\IFileAttribute;
use \ptheofan\behaviors\file\IVersion;
use yii\base\BaseObject;

/**
 * This is basic version implementation. It represents the 'original-upload' unprocessed version
 *
 * @property-read string $path
 * @property-read string $filename
 * @property-read string $name
 * @property-read string $storageLocation
 * @property-read null|string $url
 */
class BaseVersion extends BaseObject implements IVersion
{
    public string $basePath;
    public ?string $baseUrl = null;
    public string $name = 'Base Version';
    protected IFileAttribute $fileAttribute;

    public function getName(): string
    {
        return $this->name;
    }

    public function bindTo(IFileAttribute $attr): void
    {
        $this->fileAttribute = $attr;
    }

    public function getUrl(): ?string
    {
        if ($this->baseUrl && $this->getFilename()) {
            return sprintf('%s/%s', $this->baseUrl, $this->getFilename());
        }

        return null;
    }

    public function getPath(): string
    {
        return $this->basePath;
    }

    public function getFilename(): ?string
    {
        // the actual basic filename ie. file.ext
        $basename = $this->fileAttribute->getBasename();
        if (!$basename) {
            return null;
        }

        return $basename;
    }

    /**
     * Retrieve the path from storage to the file. This will not include the base path of the storage manager.
     * If you are using the LocalFileSystem you will need to prepend the path from there to get the
     * real path on your local file system.
     *
     * For example,
     * $realPathOnLocalFilesystem = $model->file->storageManager->basePath . $model->file->getStorageLocation('your-version');
     *
     * @return string|null
     */
    public function getStorageLocation(): ?string
    {
        if (!$this->basePath || !$this->getFilename()) {
            return null;
        }

        return sprintf('%s/%s', $this->basePath, $this->getFilename());
    }

    public function exists(): bool
    {
        $target = $this->getStorageLocation();
        if (!$target) {
            // if storage location is null we don't have any stored image
            return false;
        }

        return $this->fileAttribute->getStorageManager()->exists($target());
    }

    public function delete(): void
    {
        if ($this->exists()) {
            $this->fileAttribute->getStorageManager()->deleteFile($this->getStorageLocation());
        }
    }

    public function create(): void
    {
        $upload = $this->fileAttribute->getUpload();
        $this->fileAttribute->getStorageManager()->storeFile($upload->getTempName(), $this->getStorageLocation());
    }
}
