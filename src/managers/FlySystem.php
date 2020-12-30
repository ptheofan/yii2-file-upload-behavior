<?php
namespace ptheofan\behaviors\file\managers;

use common\models\behaviors\file\IStorageManager;
use creocoder\flysystem\Filesystem;
use Yii;
use yii\base\InvalidValueException;

class FlySystem implements IStorageManager
{
    public bool $remote = false;
    public string $component = 'storage';

    public function isRemote(): bool
    {
        return $this->remote;
    }

    protected function getFS(): Filesystem
    {
        $storage = Yii::$app->get($this->component);
        if (!$storage) {
            throw new InvalidValueException("Component {$this->component} is missing.");
        }

        return $storage;
    }

    public function storeFile(string $source, string $dest): void
    {
        if ($this->exists($dest)) {
            $this->deleteFile($dest);
        }

        $stream = fopen($source, 'rb+');
        $this->getFS()->writeStream($dest, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    public function storeStream($stream, string $dest): void
    {
        if ($this->exists($dest)) {
            $this->deleteFile($dest);
        }

        $this->getFS()->writeStream($dest, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    public function storeString(string $contents, string $dest): void
    {
        if ($this->exists($dest)) {
            $this->deleteFile($dest);
        }

        $this->getFS()->put($dest, $contents);
    }

    public function deleteFile(string $file): void
    {
        $this->getFS()->delete($file);
    }

    public function exists(string $file): bool
    {
        return $this->getFS()->has($file);
    }
}
