<?php
namespace ptheofan\behaviors\file;

interface IFilenameGenerator
{
    public function generateFilename(string $file): string;
    public function bindTo(IFileAttribute $attr): void;
}
