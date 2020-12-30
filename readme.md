This is an example of how to configure the behavior and use it to allow users to upload their avatar.
In this example we want to achieve the following

1. access the property as `avatar`
    1. see `modelVirtualAttribute`
1. store the filename to the database in the column `avatar_hash`.
    1. see `modelAttribute`
1. prefix the saved filename with the ID of the record
    1. as such we configure the behavior to save the image after the model has been inserted `generateAfterInsert => true`
1. ensure image is in PNG format
1. keep the original uploaded image
1. generate 3 different versions (thumbnails)
    1. sm (width = 64 pixels)
    1. md (width = 256 pixels)
    1. lg (width = 512 pixels)

When the request arrives simply push the `UploadedFile` instance to the `avatar` model property.
When you want to retrieve a particular version of the uploaded file simply call
```php
$url = $model->avatar->getVersion('sm')->getUrl();
```

You can also print the object to get detailed information
```php
echo $model->avatar;
```


```php
public function behaviors(): array
    {
        return [
            'avatar' => [
                'class' => FileAttribute::class,
                'modelAttribute' => 'avatar_hash',
                'modelVirtualAttribute' => 'avatar',
                'generateAfterInsert' => true,
                'filenameGenerator' => [
                    'class' => CallbackFilenameGenerator::class,
                    'withExt' => false,
                    'callback' => static function(string $file, ActiveRecord $model, IFileAttribute $attr) {
                        return sprintf('%s-%s', $model->id, sha1_file($file));
                    }
                ],
                'versions' => [
                    [
                        'class' => PngResizedVersion::class,
                        'name' => 'sm',
                        'basePath' => '/avatars',
                        'baseUrl' => 'https://cdn.example.com',
                        'width' => 64,
                        'suffix' => '-sm',
                    ],
                    [
                        'class' => PngResizedVersion::class,
                        'name' => 'md',
                        'basePath' => '/avatars',
                        'baseUrl' => 'https://cdn.example.com',
                        'width' => 256,
                        'suffix' => '-md',
                    ],
                    [
                        'class' => PngResizedVersion::class,
                        'name' => 'lg',
                        'basePath' => '/avatars',
                        'baseUrl' => 'https://cdn.example.com',
                        'width' => 512,
                        'suffix' => '-lg',
                    ],
                ],
            ],
        ];
    }
    ```