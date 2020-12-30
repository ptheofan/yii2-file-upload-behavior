<?php
namespace ptheofan\behaviors\file;

use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class Upload
{
    private ?string $contents = null;
    private ?string $tempName = null;
    private ?string $name = null;
    private ?string $type = null;
    private ?int $size = null;
    private ?int $error = null;

    /**
     * Upload constructor.
     *
     * @param $upload
     */
    public function __construct($upload)
    {
        if ($upload instanceof UploadedFile) {
            $this->tempName = $upload->tempName;
            $this->name = $upload->name;
            $this->type = $upload->type;
            $this->size = $upload->size;
            $this->error = $upload->error;
        } else {
            $this->contents = $upload;
        }
    }

    public function __destruct()
    {
        if ($this->tempName) {
            unlink($this->tempName);
            $this->tempName = null;
        }
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @return int|null
     */
    public function getError(): ?int
    {
        return $this->error;
    }

    public function hasError(): bool
    {
        return $this->error !== null && $this->error !== UPLOAD_ERR_OK;
    }

    /**
     * @return string|null
     */
    public function getContents(): ?string
    {
        if ($this->contents) {
            return $this->contents;
        }

        if ($this->tempName) {
            $this->contents = file_get_contents($this->tempName);
            if ($this->contents === false) {
                throw new \RuntimeException('Cannot read contents of uploaded file');
            }

            return $this->contents;
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getTempName(): ?string
    {
        if ($this->tempName) {
            return $this->tempName;
        }

        if ($this->contents) {
            $ext = $this->name ? pathinfo($this->name, PATHINFO_EXTENSION) : null;
            $this->tempName = FileHelper::tempFile(null, $ext);

            return $this->tempName;
        }

        return null;
    }
}
