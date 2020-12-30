<?php
namespace ptheofan\behaviors\file\generators;

use ptheofan\behaviors\file\IFileAttribute;
use ptheofan\behaviors\file\IFilenameGenerator;
use RuntimeException;

/**
 * Generate a filename based on sha1 or md5 of the file contents
 *
 * Use this when you want to ensure the filename is matched to contents. Typically good choice
 * when you want to ensure a file is uploaded only once and you do not allow duplicates (per folder)
 */
class HashedFilenameGenerator implements IFilenameGenerator
{
    public const ALG_SHA1 = 'sha1';
    public const ALG_MD5 = 'md5';

    /**
     * Generated hash will include the file extension.
     *  true => DEADBEEF.png
     *  false => DEADBEEF
     *
     * @var bool
     */
    public bool $withExt = true;

    public string $alg = self::ALG_SHA1;

    protected IFileAttribute $attr;

    public function bindTo(IFileAttribute $attr): void
    {
        $this->attr = $attr;
    }

    protected function generateBasename(string $file): string
    {
        switch ($this->alg) {
            case self::ALG_SHA1:
                return sha1_file($file);
            case self::ALG_MD5:
                return md5_file($file);
            default:
                throw new RuntimeException("Unknown algorithm {$this->alg}");
        }
    }

    public function generateFilename(string $file): string
    {
        $basename = $this->generateBasename($file);
        if (!$this->withExt) {
            return $basename;
        }

        $ext = pathinfo($file, PATHINFO_EXTENSION);
        return sprintf('%s.%s', $basename, $ext);
    }
}
