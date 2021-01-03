<?php
namespace ptheofan\behaviors\file\versions;

use RuntimeException;
use ptheofan\helpers\ImageHelper;

/**
 * Save the upload as a PNG in the original dimensions
 */
class PngBaseVersion extends ImgBaseVersion
{
    public string $name = 'PNG Base Version';
    public string $format = 'png';
    public string $ext = 'png';
}
