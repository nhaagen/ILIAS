# Filesystem Service

## Conceptual Summary

To eliminate security issues like path traversal, we introduced
a new Filesystem Service which streamlines the filesystem access for ILIAS.
The service provides a modular way for extension, which enables the ILIAS
community to seamless extend the service with additional supported filesystem
types.

There are four directories which are accessed via the service:

* Data directory within the ILIAS webroot
* ILIAS data directory
* Customizing directory
* Temporary directory

## ILIAS DI integration

To use the new filesystem service a new key is introduced into the DIC named "filesystem".
It's possible to access the 4 storage locations via the methods described bellow.
Each of the 4 Methods return a filesystem object which satisfies the Filesystem interface.

```php
<?php
global $DIC;

//new filesystem service key
$DIC["filesystem"];
$DIC->filesystem();

//access the 4 predefined storage locations
$DIC["filesystem"]->web();                //Data directory within the ILIAS web root
$DIC["filesystem"]->storage();            //ILIAS data directory
$DIC["filesystem"]->customizing();        //The Customizing directory within the ILIAS web root
$DIC["filesystem"]->temp();               //Temporary directory
```

## Getting started

### Core concepts

#### Files first

The filesystem has a file first approach. There are storage systems like AWS S3 which are linear. These systems
use the path to a file as identifier instead of the directories the file is nested in.

This means the directories are second class and not always needed. Because of that fact directories will be
automatically
created on filesystem that require them. This makes writing files a lot easier and ensures a consistent behaviours
across different filesystems.

#### Relative paths

Because to the portability and abstraction of the filesystem each path is relative.
The filesystem root paths local or remote are viewed as endpoints. Because of that filesystems can be switched out as
needed.
It also allows ILIAS to operate on different filesystems at once.

#### Adapters

The main entry point for the filesystem API is the Filesystem interface. The way it works is because
of the adapter pattern which eliminates the differences of the supported filesystems.

### File operations

There are several operation which can be used to manipulate files on the given filesystem.
For each operation you need to fetch an actual filesystem via the ILIAS container.

```php
<?php
$webDataRoot = $DIC->filesystem()->web();
// ...  do awesome stuff
```

#### Create

The write will create a new file an write the content into it. If the
file already exists the operation fails.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$webDataRoot->write('relative/path/to/file', 'awesome stuff');
```

#### Read

Reads the content from an existing file.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$content = $webDataRoot->read('relative/path/to/file');
```

#### Update

Overwrite the content of the file with a new one. The file must already exist or
the operation will fail.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$webDataRoot->update('relative/path/to/file', 'awesome stuff');
```

#### Delete

Deletes an existing file. If the file does not exist the operation will fail.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$webDataRoot->delete('relative/path/to/file');
```

#### Put

The put operation creates or updates a file.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$webDataRoot->put('relative/path/to/file', 'awesome stuff');
```

#### ReadAndDelete

The read and delete operation reads the entire content of a file and delete it after
the read operation is complete.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$content = $webDataRoot->readAndDelete('relative/path/to/file');
```

#### Has

The has operation is used to check the existence of files. Please not
that this operation only works for files.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$exists = $webDataRoot->has('relative/path/to/file');
```

#### rename

Moves a file to the given destination.
The operation fails, if the destination file already exists or the source file is not found.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$webDataRoot->rename('relative/path/to/file', 'new/path/to/file');
```

#### copy

Copies a file to an other location.
The operation fails, if the destination file already exists or the source file is not found.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$webDataRoot->copy('relative/path/to/file', 'path/to/file/copy');
```

### File information

#### MimeType

The filesystem service tries to get the most suitable mime type of the file. The operation fails if the
file could not be found or red.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$mimeType = $webDataRoot->getMimeType('relative/path/to/file');
```

#### Timestamp

Get the timestamp (mtime) of the file.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$timestamp = $webDataRoot->getTimestamp('relative/path/to/file');
```

#### Size

Fetches the file size.

```php
<?php
use ILIAS\Data\DataSize;

/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$fileSize = $webDataRoot->getSize('relative/path/to/file', DataSize::MiB);
$message = "File size: " . $fileSize->getSize() . " " . $fileSize->getSize();
```

#### Visibility

Every filesystem has own concepts and restrictions in terms of the filesystem security.
Because of that the filesystem service introduces its own abstraction which is consistent over all filesystems.

The abstraction is called visibility.
Each file can be public or private which maps to the corresponding filesystem rights.

The visibility can be changed with the help of the setVisibility method:

```php
<?php
use ILIAS\Filesystem\Visibility;
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$success = $webDataRoot->setVisibility('relative/path/to/file', Visibility::PRIVATE_ACCESS);
```

The file visibility can be fetched with the getVisibility method:

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$visibility = $webDataRoot->getVisibility('relative/path/to/file');
```

### File streaming

#### readStream

Opens the stream of an existing file.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$fileStream = $webDataRoot->readStream('relative/path/to/file');

//do stuff on your stream 
$content = $fileStream->read(20);

//and close the stream after everything is done
$fileStream->close();
```

#### Write Stream

Write the stream to a new file.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$fileStream = $webDataRoot->readStream('relative/path/to/file');

//seek at the end of the stream
$fileStream->seek($fileStream->getSize() - 1);
//append something
$fileStream->write("something");

//write stream to another file
//the stream will automatically be closed after the operation 
$webDataRoot->writeStream('relative/path/to/another/file', $fileStream);
```

#### putStream

Create a new file or update an existing one.
The content will be replaced with the stream content.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$fileStream = $webDataRoot->readStream('relative/path/to/file');

//seek at the end of the stream
$fileStream->seek($fileStream->getSize() - 1);
//append something
$fileStream->write("something");

//write stream to another file
//the stream will automatically be closed after the operation 
$webDataRoot->putStream('relative/path/to/another/file', $fileStream);
```

#### updateStream

Updates an existing file with the stream content.
The old file content will be overwritten in the process.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$fileStream = $webDataRoot->readStream('relative/path/to/file');

//seek at the end of the stream
$fileStream->seek($fileStream->getSize() - 1);
//append something
$fileStream->write("something");

//write stream to another file
//the stream will automatically be closed after the operation 
$webDataRoot->updateStream('relative/path/to/another/file', $fileStream);
```

### Directory handling

#### Create Directory

Creates a new directory.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$webDataRoot->createDir("new/directory");
```

#### Copy Directory

Creates a recursive copy of the source directory.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$webDataRoot->copyDir("source/directory", "destination/directory");
```

#### Delete Directory

Deletes the entire directory with all children.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$webDataRoot->deleteDir("new/directory");
```

#### List Directory Content

List the directory content.
The second argument is true then the directory listening will be recursive.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $webDataRoot
 */
$webDataRoot = $DIC->filesystem()->web();
$metadataArray = $webDataRoot->listContents("your/path/to/the/directory", true);
foreach ($metadataArray as $metadata) {
	echo "Data is located at " . $metadata->getPath() . " and is a " . $metadata->getType();
}
```

### Cross Filesystem Operation

The cross filesystem operation such as copy a file from one filesystem to another one can be archived with the
help of the stream interface.

```php
<?php
/**
 * @var ILIAS\Filesystem\Filesystem $temp
 */
$temp = $DIC->filesystem()->temp();

/**
 * @var ILIAS\Filesystem\Filesystem $web
 */
$web = $DIC->filesystem()->web();
$stream = $temp->readStream("source/file");
$web->writeStream("destination/file", $stream);
```

## Stream creation

The *Streams* class delivers various stream creation methods.

### from string

```php
<?php
use ILIAS\Filesystem\Stream\Streams;

$stream = Streams::ofString("stream content");

//write stream to file ...
```

### from resource

```php
<?php
use ILIAS\Filesystem\Stream\Streams;

/* 
 * Please note that the fopen call is only used for demonstration purposes and must not be used to
 * create a stream from a string.
 */
$resource = fopen('data://text/plain,HelloWorld', 'r');
$stream = Streams::ofResource($resource);

//write stream to file ...
```

### from psr7 stream

```php
<?php
use ILIAS\Filesystem\Stream\Streams;

global $DIC;

/**
 * @var \ILIAS\HTTP\Services $http
 */
$http = $DIC['http'];

//fetch http body stream
$stream = $http->request();

//convert the stream (body stream is detached afterwards!)
$stream = Streams::ofPsr7Stream($stream->getBody());

//write stream to file ...
```

## Finder

For an easy access to files and directories a `Finder` is provided for each `Filesystem` given
in the `$DIC`.

```php
<?php
/** @var ILIAS\Filesystem\Filesystem $web */
$web = $DIC->filesystem()->web();
$finder = $web->finder();
```

The finder offers multiple methods to find and list files and directories (recursively), using a bunch of optional
filters and options for the sorting of the determined `Metadata` collection. The finder is completely
immutable and provides a fluent interface.

The `Finder` instance itself is iterable. It implements the `\IteratorAggregate` interface and only operates
on the filesystem (via the `\ILIAS\Filesystem\Filesystem`) when an iteration is triggered.

By default the finder ignores DOT and VCS files.

Please have a also look at the [tests](/tests/Filesystem/Finder/FinderTest.php) to see how it works.

### Directory Filter

#### Whitelisting

The only *mandatory* criterion is the location the finder should use for the search.

```php
<?php
/** @var ILIAS\Filesystem\Filesystem $web */
$web = $DIC->filesystem()->web();
$finder = $web->finder();

foreach ($finder->files()->in(['/']) as $metadata) {
}


foreach ($finder->files()->in(['/', 'dir_1/dir_1_1']) as $metadata) {
}
```

#### Blacklisting

You can exclude directories from matching by calling `exclude()`;

```php
<?php
/** @var ILIAS\Filesystem\Filesystem $web */
$web = $DIC->filesystem()->web();
$finder = $web->finder();

foreach ($finder->files()->exclude(['dir_1/dir_1_1']) as $metadata) {
}
```

### Metadata Types Filter

By default, the `Finder` iterates over both, files and directories.
It provides methods to find only files, only directories or both (to reset the filter).

```php
<?php
/** @var ILIAS\Filesystem\Filesystem $web */
$web = $DIC->filesystem()->web();
$finder = $web->finder();

foreach ($finder->files() as $metadata) {
}

foreach ($finder->directories() as $metadata) {
}

foreach ($finder->allTypes() as $metadata) {
} 
```

### Depth Filter

By default the `Finder` scans directories recursively. The depth of traversing
through the `\ILIAS\Filesystem\Filesystem` can be restricted/limited with `depth()`.
The depth starts at index 0 (root directory).

```php
<?php
/** @var ILIAS\Filesystem\Filesystem $web */
$web = $DIC->filesystem()->web();
$finder = $web->finder();

foreach ($finder->files()->depth(0) as $metadata) {
}

foreach ($finder->files()->depth('> 2')->depth('< 5') as $metadata) {
}
```

### Date Filter

The search can be restricted to files with a certain 'last modified' date by calling `date()`.
The following operators are supported: > (since), >=, < (until), <=, ==.
The provided value MUST be parsable by `strtotime()`.

```php
<?php
/** @var ILIAS\Filesystem\Filesystem $web */
$web = $DIC->filesystem()->web();
$finder = $web->finder();

foreach ($finder->files()->date('since yesterday') as $metadata) {
}

foreach ($finder->files()->date('< 2018-31-32') as $metadata) {
}

foreach ($finder->files()->date('>= 2019-03-30 15:00 + 2hours') as $metadata) {
}

```

### Size Filter

A restriction to files with a certain file size can be achieved by calling `size()`.
The following operators are supported: : >, >=, <, <=, ==, !=.
Please provide magnitudes of kilobytes (ki, k), megabytes (mi, m)
or gigabytes (gi, g) as value.

```php
<?php
/** @var ILIAS\Filesystem\Filesystem $web */
$web = $DIC->filesystem()->web();
$finder = $web->finder();

foreach ($finder->files()->size('> 1Mi') as $metadata) {
}
```

### Sorting

The found `Metadata` can be sorted by time (see: `\ILIAS\Filesystem\Provider\FileReadAccess::getTimestamp`),
name or by type (1st directories, 2nd files). The sorting can also be reversed.

```php
<?php
/** @var ILIAS\Filesystem\Filesystem $web */
$web = $DIC->filesystem()->web();
$finder = $web->finder();

foreach ($finder->files()->sortByType() as $metadata) {
}

foreach ($finder->files()->sortByName()->reverseSorting() as $metadata) {
}

foreach ($finder->files()->sortByTime() as $metadata) {
}
```

Furthermore you can provide a custom callback, accepting two `Metadata` instances
as the only arguments. The callback must return -1, 0 or 1.

```php
<?php
/** @var ILIAS\Filesystem\Filesystem $web */
$web = $DIC->filesystem()->web();
$finder = $web->finder();

$cb = function(Filesystem\DTO\Metadata $left, Filesystem\DTO\Metadata $right) : int {
    if ('dir_1/dir_1_1/file_5.cpp' === $left->getPath()) {
        return -1;
    }

    return 1;
};

foreach ($finder->files()->sort($cb) as $metadata) {
}
```

## Authors

* **Nicolas Schaefli** - *interface definition* - [d3r1w](https://github.com/d3r1w)

## Versioning

We use [SemVer](http://semver.org/) for versioning.

## Acknowledgments

* [keep a changelog](http://keepachangelog.com/) The guide used to create and update the service changelog.
* [FlySystem](https://flysystem.thephpleague.com/) Filesystem abstraction written by the php league.
