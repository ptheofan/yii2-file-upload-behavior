<?php
namespace ptheofan\behaviors\file\generators;

use common\models\behaviors\file\IFileAttribute;
use common\models\behaviors\file\IFilenameGenerator;
use common\models\Community;

/**
 * Use a callback function to define the filename.
 * Good choice if you want your filename to match the mode pk (id).
 *
 * When going down this path remember to properly configure the IFileAttribute
 * ie.
 *      generateAfterInsert => true
 *
 * @example model attribute saved as PK-SHA1
 *  'filenameGenerator' => [
 *      'class' => CallbackFilenameGenerator::class,
 *      'withExt' => false,
 *      'callback' => static function(string $file, Community $model, IFileAttribute $attr) {
 *          return sprintf('%s-%s', $model->id, sha1_file($file));
 *      }
 *  ],
 *
 *
 * @example model attribute saved as PK-SHA1.ext
 *  'filenameGenerator' => [
 *      'class' => CallbackFilenameGenerator::class,
 *      'withExt' => true,
 *      'callback' => static function(string $file, Community $model, IFileAttribute $attr) {
 *          return sprintf('%s-%s', $model->id, sha1_file($file));
 *      }
 *  ],
 */
class CallbackFilenameGenerator implements IFilenameGenerator
{
    protected IFileAttribute $attr;

    /**
     * signature:
     * function(string $file, Component $owner, IFileAttribute $attr)
     *
     * @var callable
     */
    public $callback;

    public function bindTo(IFileAttribute $attr): void
    {
        $this->attr = $attr;
    }

    /**
     * Generated hash will include the file extension.
     *  true => file.png
     *  false => file
     *
     * @var bool
     */
    public bool $withExt = true;

    protected function generateBasename(string $file): string
    {
        return call_user_func($this->callback, $file, $this->attr->getOwner(), $this->attr);
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
