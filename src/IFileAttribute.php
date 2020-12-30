<?php
namespace ptheofan\behaviors\file;

use yii\base\Component;

/**
 * Interface IImageAttribute
 *
 * @package common\models
 */
interface IFileAttribute
{
    /**
     * Get the name stored in the db attribute
     *
     * @return string|null
     */
    public function getBasename(): ?string;

    /**
     * Set the name to store to the db attribute
     *
     * @param string|null $name
     */
    public function setBasename(?string $name): void;

    public function hasFile(): bool;
    public function getFilenameGenerator(): IFilenameGenerator;
    public function getStorageManager(): IStorageManager;
    public function getVersion(string $version): IVersion;
    public function getUpload(): Upload;
    public function getOwner(): Component;
    public function getFilename(string $version): ?string;
    public function getPath(string $version): ?string;
    public function getUrl(string $version, $default = null): ?string;
    public function getStorageLocation(string $version): ?string;
}
