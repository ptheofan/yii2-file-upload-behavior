<?php
namespace ptheofan\behaviors\file\generators;

/**
 * Generate a filename based on HashedFilenameGenerator and
 * suffix a random unique id with max entropy
 *
 * Use this when you want unique files based on hash but want
 * to allow multiple copies of a file in the same folder
 */
class RandomHashedFilenameGenerator extends HashedFilenameGenerator
{
    protected function generateBasename(string $file): string
    {
        $basename = parent::generateBasename($file);
        return uniqid($basename, true);
    }
}
