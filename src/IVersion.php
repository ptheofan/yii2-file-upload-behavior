<?php
namespace ptheofan\behaviors\file;

interface IVersion
{
    public function bindTo(IFileAttribute $attr): void;

    /**
     * @return string friendly name of the version (example Original File)
     */
    public function getName(): string;

    /**
     * @return string|null the complete url to file, null if not applicable
     *                     (example https://cdn.example.com/path/to/file.ext)
     */
    public function getUrl(): ?string;

    /**
     * @return string just the path ends with slash (example /path/to/)
     */
    public function getPath(): string;

    /**
     * @return string just the filename (example file.ext)
     */
    public function getFilename(): ?string;

    /**
     * @return string combined path and filename (example /path/to/file.ext)
     */
    public function getStorageLocation(): ?string;

    /**
     * @return bool true if versioned file exists
     */
    public function exists(): bool;

    /**
     * Delete the file of this version
     */
    public function delete(): void;

    /**
     * Create the file of this version
     */
    public function create(): void;
}
