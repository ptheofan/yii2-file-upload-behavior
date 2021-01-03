<?php
namespace ptheofan\behaviors\file\versions;

use RuntimeException;
use ptheofan\helpers\ImageHelper;

/**
 * Save the upload as a JPG in the original dimensions
 */
class JpgBaseVersion extends ImgBaseVersion
{
    public string $name = 'JPG Base Version';
    public string $format = 'jpg';
    public string $ext = 'jpg';
}
