[< devguide](../devguide.md#tools)

# composer

If you do not have composer already, get it here:
https://getcomposer.org/download/

Head to the root-directory of your ILIAS directory.
The desired packages are listed in composer.json.
You can install them by running
```
composer install --no-dev
```
Since Composer also provides the autoloading-capabilites for ILIAS,
you will come back to it from time to time; 
a very common command to rescan and build static artifacts is dump-autoload:
```
composer du
```