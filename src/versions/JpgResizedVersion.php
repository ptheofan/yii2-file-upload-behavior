<?php
namespace ptheofan\behaviors\file\versions;

use RuntimeException;
use ptheofan\helpers\ImageHelper;

/**
 * Generate a resized JPG image
 */
class JpgResizedVersion extends ImgResizedVersion
{
    public string $name = 'JPG Resized Version';
    public ?string $prefix = null;
    public ?string $suffix = null;
    public ?int $width = null;
    public ?int $height = null;
    public string $format = 'jpg';
    public string $ext = 'jpg';
    
}
