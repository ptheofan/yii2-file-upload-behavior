<?php
namespace ptheofan\behaviors\file;

interface IStorageManager
{
    /**
     * @return bool true if file system is remote
     */
    public function isRemote(): bool;

    /**
     * Copy a file from OS filesystem to storage system
     *
     * @param string $source /path/to/file on local system
     * @param string $dest /path/to/file on storage system
     */
    public function storeFile(string $source, string $dest): void;

    /**
     * Save a stream to storage system
     *
     * @param resource $stream stream resource
     * @param string $dest /path/to/file on storage system
     */
    public function storeStream($stream, string $dest): void;

    /**
     * Save a string (contents) to storage system
     *
     * @param string $contents string to store (can be binary)
     * @param string $dest /path/to/file on storage system
     */
    public function storeString(string $contents, string $dest): void;

    /**
     * Delete a file from storage system
     *
     * @param string $file /path/to/file on storage system
     */
    public function deleteFile(string $file): void;

    /**
     * Determine if file exists on the storage system
     *
     * @param string $file /path/to/file on storage system
     * @return bool true if file exists
     */
    public function exists(string $file): bool;
}
