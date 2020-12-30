<?php
namespace ptheofan\behaviors\file;

use common\components\UploadedFile;
use common\models\behaviors\file\generators\HashedFilenameGenerator;
use common\models\behaviors\file\managers\FlySystem;
use RuntimeException;
use Yii;
use yii\base\Behavior;
use yii\base\Component;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\UnknownPropertyException;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;

/**
 *
 * @property null|string $basename
 */
class FileAttribute extends Behavior implements IFileAttribute
{
    public string $modelAttribute = 'file_hash';
    public string $modelVirtualAttribute = 'file';

    /**
     * using after_insert to allow the option to use the pk (id) of a record as filename. This will 'cause
     * and additional update operation but will strictly only update the $modelAttribute whilst it will
     * disable validations for the operation.
     *
     * If you do not require the pk of the model for file generation set this to false for improved
     * performance and decreased validation headaches
     *
     * @var bool
     */
    public bool $generateAfterInsert = false;

    /**
     * @see Yii::createObject()
     * @var string|array|IStorageManager
     */
    public $storageManager = FlySystem::class;

    /**
     * @see Yii::createObject()
     * @var string|array|IFilenameGenerator
     */
    public $filenameGenerator = HashedFilenameGenerator::class;

    /**
     * @example
     * 'versions' => [
     *    [
     *      'class' => PngBaseVersion::class,
     *      'name' => 'upload',
     *      'basePath' => '/community',
     *      'baseUrl' => Yii::$app->host->storage->url(),
     *    ],
     *    [
     *       'class' => PngResizedVersion::class,
     *       'name' => 'sm',
     *       'basePath' => '/community',
     *       'baseUrl' => Yii::$app->host->storage->url(),
     *       'width' => 32,
     *       'suffix' => '-sm',
     *    ],
     * ],
     *
     * @see Yii::createObject()
     * @var array of Yii::createObject items
     */
    public array $versions = [];


    private ?IStorageManager $_storageManager = null;
    private ?IFilenameGenerator $_filenameGenerator = null;

    /**
     * @var IVersion[]
     */
    public ?array $_versions = null;

    protected ?Upload $upload = null;

    /**
     * @return array
     */
    public function events(): array
    {
        $insert = $this->generateAfterInsert ? BaseActiveRecord::EVENT_AFTER_INSERT : BaseActiveRecord::EVENT_BEFORE_INSERT;
        return [
            $insert => 'onModelInsert',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'onModelUpdate',
            BaseActiveRecord::EVENT_AFTER_DELETE => 'onModelDelete',
        ];
    }

    public function getBasename(): ?string
    {
        return $this->owner->{$this->modelAttribute};
    }

    public function setBasename(?string $name): void
    {
        $this->owner->{$this->modelAttribute} = $name;
    }

    public function getStorageManager(): IStorageManager
    {
        if ($this->_storageManager === null) {
            if ($this->storageManager instanceof IStorageManager) {
                $this->_storageManager = $this->storageManager;
            } else {
                $this->_storageManager = Yii::createObject($this->storageManager);
            }
        }

        return $this->_storageManager;
    }

    public function getFilenameGenerator(): IFilenameGenerator
    {
        if ($this->_filenameGenerator === null) {
            if ($this->filenameGenerator instanceof IFilenameGenerator) {
                $this->_filenameGenerator = $this->filenameGenerator;
            } else {
                $this->_filenameGenerator = Yii::createObject($this->filenameGenerator);
            }

            $this->_filenameGenerator->bindTo($this);
        }

        return $this->_filenameGenerator;
    }

    public function getVersion(string $version): IVersion
    {
        $versions = $this->getVersions();
        if (!isset($versions[$version])) {
            throw new RuntimeException("Version {$version} does not exist.");
        }

        return $versions[$version];
    }

    /**
     * @return IVersion[]
     * @throws InvalidConfigException
     */
    public function getVersions(): array
    {
        if ($this->_versions === null) {
            foreach ($this->versions as $version) {
                if (!$version instanceof IVersion) {
                    $version = Yii::createObject($version);
                }

                if (!$version instanceof IVersion) {
                    throw new RuntimeException('Invalid version object');
                }
                $version->bindTo($this);

                $this->_versions[$version->getName()] = $version;
            }
        }

        return $this->_versions;
    }

    public function getUpload(): Upload
    {
        return $this->upload;
    }

    /**
     * New Record is created, if Upload is attached save it
     *
     * @param Event $event
     */
    public function onModelInsert(Event $event): void
    {
        if ($this->upload && !$this->upload->hasError()) {
            $this->setBasename($this->getFilenameGenerator()->generateFilename($this->upload->getTempName()));
            foreach ($this->getVersions() as $name => $version) {
                $version->create();
            }

            if ($this->generateAfterInsert) {
                if (!$this->owner->hasMethod('save')) {
                    throw new RuntimeException('Owner has no save function');
                }

                // Disable model update event handler for this save operation
                $this->owner->off(BaseActiveRecord::EVENT_BEFORE_UPDATE, [$this, 'onModelUpdate']);
                /** @noinspection PhpPossiblePolymorphicInvocationInspection */
                $this->owner->save(false, [$this->modelAttribute]);
                // Re-enable model update event handler
                $this->owner->on(BaseActiveRecord::EVENT_BEFORE_UPDATE, [$this, 'onModelUpdate']);
            }

            $event->handled = true;
        }
    }

    /**
     * Model is being updated, if new $value is set, replace existing file(s)
     *
     * @param Event $event
     */
    public function onModelUpdate(Event $event): void
    {
        if ($this->upload && !$this->upload->hasError()) {
            foreach ($this->getVersions() as $version) {
                $version->delete();
            }

            $this->setBasename($this->getFilenameGenerator()->generateFilename($this->upload->getTempName()));
            foreach ($this->getVersions() as $version) {
                $version->create();
            }

            /** @noinspection PhpPossiblePolymorphicInvocationInspection */
            $this->owner->save(false, [$this->modelAttribute]);
        }
    }

    /**
     * Model is being deleted, remove the file and versions
     *
     * @param Event $event
     */
    public function onModelDelete(Event $event): void
    {
        foreach ($this->getVersions() as $version) {
            $version->delete();
        }

        $this->setBasename(null);
    }

    /**
     * @return bool
     */
    public function hasFile(): bool
    {
        return $this->owner->{$this->modelAttribute} !== null;
    }

    /**
     * @param string $version
     * @return string
     */
    public function getFilename(string $version): ?string
    {
        return $this->getVersion($version)->getFilename();
    }

    /**
     * @param string $version
     * @return string
     */
    public function getPath(string $version): ?string
    {
        return $this->getVersion($version)->getPath();
    }

    /**
     * @param string $version
     * @param string $default
     * @return mixed
     */
    public function getUrl(string $version, $default = null): ?string
    {
        if (!$this->hasFile()) {
            return $default;
        }

        return $this->getVersion($version)->getUrl();
    }

    /**
     * @param string $version
     * @return string|null
     */
    public function getStorageLocation(string $version): ?string
    {
        return $this->getVersion($version)->getStorageLocation();
    }

    public function getOwner(): Component
    {
        return $this->owner;
    }

    /**
     * @param string $name
     * @param bool $checkVars
     * @return bool
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @noinspection PhpMissingParamTypeInspection
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function canGetProperty($name, $checkVars = true)
    {
        if ($name === $this->modelVirtualAttribute) {
            return true;
        }

        return parent::canGetProperty($name, $checkVars);
    }

    /**
     * @param string $name
     * @param bool $checkVars
     * @return bool
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection ReturnTypeCanBeDeclaredInspection
     * @noinspection PhpMissingParamTypeInspection
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if ($name === $this->modelVirtualAttribute) {
            return true;
        }

        return parent::canSetProperty($name, $checkVars);
    }

    /**
     * @param string $name
     * @return mixed|string
     * @noinspection PhpMissingReturnTypeInspection
     * @noinspection PhpMissingParamTypeInspection
     * @throws UnknownPropertyException
     */
    public function __get($name)
    {
        if ($name === $this->modelVirtualAttribute) {
            return $this;
        }

        return parent::__get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @noinspection PhpMissingParamTypeInspection
     * @throws UnknownPropertyException
     */
    public function __set($name, $value)
    {
        if ($name === $this->modelVirtualAttribute) {
            if ($value === null) {
                $this->upload = null;
            } elseif ($value instanceof UploadedFile) {
                $this->upload = new Upload($value);
            } elseif (is_string($value) && !empty($value)) {
                $decoded = base64_decode($value);
                if ($decoded !== false) {
                    $this->upload = new Upload($decoded);
                }
            }

            throw new RuntimeException('IFileAttribute upload supports only Multipart Upload or base64 encoded string.');
        }

        parent::__set($name, $value);
    }

    public function __toString(): string
    {
        $rVal = [];
        $rVal[] = sprintf('modelAttribute = %s', $this->modelAttribute);
        $rVal[] = sprintf('modelVirtualAttribute = %s', $this->modelVirtualAttribute);
        $rVal[] = sprintf('Basename = %s', $this->getBasename() ?: 'null');
        if ($this->hasFile()) {
            foreach ($this->getVersions() as $version) {
                $rVal[] = sprintf('filename[%s] = %s', $version->getName(), $version->getFilename());
                $rVal[] = sprintf('path[%s] = %s', $version->getName(), $version->getPath());
                $rVal[] = sprintf('url[%s] = %s', $version->getName(), $version->getUrl());
                $rVal[] = sprintf('storageLocation[%s] = %s', $version->getName(), $version->getStorageLocation());
            }
        } else {
            $rVal[] = sprintf('Versions = %s', implode(', ', ArrayHelper::getColumn($this->getVersions(), 'name')));
        }

        return implode("\n", $rVal);
    }
}
