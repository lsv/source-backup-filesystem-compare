## Compare a source filesystem, with a backup filesystem

It will look for every file on the source filesystem, and report if the file is missing on the backup filesystem, and if it exists, it will report if the backup file is newer/older or has a different size than the source file.

#### Install

```
composer require lsv/source-backup-filesystem-compare
```

#### Usage

First we need to create a source filesystem and a backup filesystem.

We are using [league/flysystem](https://flysystem.thephpleague.com/docs/) so we can use many different storages  

```php
use League\Flysystem\Filesystem;
use Lsv\BackupCompareFilesystems\CompareFilesystems;

// For adapters see the league/flysystem docs
$source = new Filesystem($sourceAdapter);
$backup = new Filesystem($backupAdapter);

foreach ((new CompareFilesystems($backup, $source))->compare() as $file) {
    // Now $file are a Lsv\Lsv\BackupCompareFilesystems\Model object
    if ($errors = $file->getErrors()) {
        foreach ($errors as $error) {
            echo $error; // $error is a integer which corresponts to the following constants
            // 1 = \Lsv\BackupCompareFilesystems\Model::SOURCE_FILE_DOES_NOT_EXISTS_IN_BACKUP
            // 2 = \Lsv\BackupCompareFilesystems\Model::SOURCE_FILE_IS_SMALLER_THAN_BACKUP
            // 3 = \Lsv\BackupCompareFilesystems\Model::SOURCE_FILE_IS_BIGGER_THAN_BACKUP
            // 4 = \Lsv\BackupCompareFilesystems\Model::SOURCE_FILE_IS_OLDER_THAN_BACUP
            // 5 = \Lsv\BackupCompareFilesystems\Model::SOURCE_FILE_IS_NEWER_THAN_BACKUP
        }
    } else {
        echo 'Backup file is matching the source file';
        // Source file is matching the backup file
    }
}
