<?php

namespace ptheofan\behaviors\file\managers;

use common\exceptions\FolderNotWriteable;
use http\Encoding\Stream;
use ptheofan\behaviors\exceptions\UnableToStoreFile;
use ptheofan\behaviors\file\IStorageManager;
use yii\base\InvalidConfigException;

class LocalFileSystem implements IStorageManager
{
    public $basePath = null;

    protected function getFullpath(string $partial): string
    {
        if ($this->basePath === null) {
            throw new InvalidConfigException('Attribute basePath has not been set');
        }

        return sprintf('%s%s', rtrim($this->basePath, '/'), $partial);
    }

    public function isRemote(): bool
    {
        return false;
    }

    public function storeFile(string $source, string $dest): void
    {
        if (!copy($source, $this->getFullpath($dest))) {
            throw new UnableToStoreFile(sprintf('Unable to copy file from %s to %s', $source, $dest));
        }
    }

    public function storeStream($stream, string $dest): void
    {
        if (file_put_contents($this->getFullpath($dest), $stream) === false) {
            throw new UnableToStoreFile(sprintf('Unable to save stream to %s', $dest));
        }
    }

    public function storeString(string $contents, string $dest): void
    {
        if (file_put_contents($this->getFullpath($dest), $contents) === false) {
            throw new UnableToStoreFile(sprintf('Unable to save string to %s', $dest));
        }
    }

    public function deleteFile(string $file): void
    {
        try {
            unlink($this->getFullpath($dest));
        } catch (\Exception $ignored) { }
    }

    public function exists(string $file): bool
    {
        return is_dir($this->getFullpath($file)) && is_writable($this->getFullpath($file));
    }
}