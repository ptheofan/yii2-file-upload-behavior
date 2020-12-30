Documentation is incomplete and will add more info and examples as time allows. Feel free to open an issue if there's something you want to ask or add to the documentation.
Currently it comes with a facade for the FlySystem only. In due time I will add also a facade for basic local filesystem

The entire system is designed to be very customisable and extensible. There are a couple of points that can be (and will be) improved
to make it as extensible and customisable as possible whilst keeping it sane.

This is an example of how to configure the behavior and use it to allow users to upload their avatar.
In this example we want to achieve the following

1. access the property as `avatar`
    1. see `modelVirtualAttribute`
1. store the filename to the database in the column `avatar_hash`.
    1. see `modelAttribute`
1. Filename prefixed with row ID, filename the SHA1 of the file and suffix the version
    1. For this we set the `filenameGenerator` to use the `CallbackFilenameGenerator`
    1. Configure it to not include the extension (versions will take care of .ext in this case)
    1. 
1. Keep the following versions
    1. Unresized upload in PNG format `PngBaseVersion`. Final name will look something like 512-deadbeef.png
    1. sm (width = 64 pixels) with suffix `-sm`. Final name will look something like 512-deadbeef-sm.png
    1. md (width = 256 pixels) with suffix `-md`. Final name will look something like 512-deadbeef-md.png
    1. lg (width = 512 pixels) with suffix `-lg`. Final name will look something like 512-deadbeef-lg.png


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
                        // will produce 
                        'class' => PngBaseVersion::class,
                        'name' => 'sm',
                        'basePath' => '/avatars',
                        'baseUrl' => 'https://cdn.example.com',
                        'width' => 64,
                        'suffix' => '-sm',
                    ],
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