## about
I realised that i was doing a lot of copy paste and i was writing same code multiple times for different features, so i started making a custom system to avoid that.
## installation
Run `composer require genocide/radiocrud` to install package.

After installing Radiocrud you should run `php artisan vendor:publish --provider Genocide\Radiocrud\RadiocrudServiceProvider`. because there is a migration you need to have it in your app to use 'KeyValueConfigService'.

Then run `php artisan migrate` to migrate newly added migration.

### you're not done!
this is last step of installation, i promise !
now you should add some file system disks, so Radiocrud can work with files.
add code below to `config\filesystems.php` (add it to "disks" array)
```
'public_uploads' => [
    'driver' => 'local',
    'root' => public_path(),
    'throw' => false,
],

'base' => [
    'driver' => 'local',
    'root' => base_path(),
    'throw' => false,
],
```
Radiocrud will use this file system disks to do things such as creating new Action class with command.
