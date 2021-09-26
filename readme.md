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

When the request arrives simply push the `UploadedFile` instance to the `avatar` model property, save the model and voila, image stored as per the provided configuration and all versions are generated.
```php
$model->avatar = UploadedFile::getInstance($model, 'file');
$model->save();
```

When you want to retrieve a particular version of the uploaded file simply call
```php
$url = $model->avatar->getVersion('sm')->getUrl();
```


You can also print the object to get detailed helpful information
```php
echo $model->avatar;
```

In the following example we configure our model to use the database column `avatar_hash` to store the file information in the database and set virtual attribute to avatar. This means
1. `$model->avatar_hash` contains the value produced by the generator (you typically don't care to touch this column). 
2. `$model->avatar` is your accessor to the image. Calling `$model->avatar->getVersion('sm')->getUrl()` will return the complete URL to the small version of the file.
3. You can assign an uploaded file as simple as `$model->avatar = UploadedFile::getInstance($model, 'avatar')`
4. You can push a base64 encoded image as simply as `$model->avatar = $myBase64EncodedImage`
5. You can push a binary string simply by `$model->avatar = file_get_contents('my_file.png')`
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
                        'baseUrl' => 'https://cdn.example.com/avatars',
                        'width' => 64,
                        'suffix' => '-sm',
                    ],
                    [
                        'class' => PngResizedVersion::class,
                        'name' => 'sm',
                        'basePath' => '/avatars',
                        'baseUrl' => 'https://cdn.example.com/avatars',
                        'width' => 64,
                        'suffix' => '-sm',
                    ],
                    [
                        'class' => PngResizedVersion::class,
                        'name' => 'md',
                        'basePath' => '/avatars',
                        'baseUrl' => 'https://cdn.example.com/avatars',
                        'width' => 256,
                        'suffix' => '-md',
                    ],
                    [
                        'class' => PngResizedVersion::class,
                        'name' => 'lg',
                        'basePath' => '/avatars',
                        'baseUrl' => 'https://cdn.example.com/avatars',
                        'width' => 512,
                        'suffix' => '-lg',
                    ],
                ],
            ],
        ];
    }
    ```
