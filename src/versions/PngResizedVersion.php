<?php
namespace ptheofan\behaviors\file\versions;

use RuntimeException;
use ptheofan\helpers\ImageHelper;

/**
 * Generate a resized PNG image
 */
class PngResizedVersion extends ImgResizedVersion
{
    public string $name = 'PNG Resized Version';
    public ?string $prefix = null;
    public ?string $suffix = null;
    public ?int $width = null;
    public ?int $height = null;
    public string $format = 'png';
    public string $ext = 'png';
    
}
