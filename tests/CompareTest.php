<?php

declare(strict_types=1);

namespace Lsv\BackupCompareFilesystemsTests;

use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use Lsv\BackupCompareFilesystems\CompareFilesystems;
use Lsv\BackupCompareFilesystems\Model\File;
use PHPUnit\Framework\TestCase;

class CompareTest extends TestCase
{
    /**
     * @test
     */
    public function will_compare_backup_file_is_correct(): void
    {
        $path = 'temp1/file.txt';
        $content = 'abcdefgh';
        $config = (new Config())->set('timestamp', time());
        $sourceConfig = clone $config;
        $backupConfig = clone $config;

        $source = new MemoryAdapter();
        $source->write($path, $content, $sourceConfig);
        $backup = new MemoryAdapter();
        $backup->write($path, $content, $backupConfig);

        $files = [];
        foreach ((new CompareFilesystems(new Filesystem($backup), new Filesystem($source)))->compare() as $file) {
            $files[] = $file;
        }

        $this->assertCount(1, $files);
        $this->assertCount(0, $files[0]->getErrors());
        $this->assertSame('temp1/file.txt', $files[0]->getPath());
        $this->assertSame('file.txt', $files[0]->getFilename());
    }

    /**
     * @test
     */
    public function will_compare_that_backup_file_does_not_exists(): void
    {
        $path = 'temp1/file.txt';
        $content = 'abcdefgh';
        $config = (new Config())->set('timestamp', time());
        $sourceConfig = clone $config;

        $source = new MemoryAdapter();
        $source->write($path, $content, $sourceConfig);
        $backup = new MemoryAdapter();

        $files = [];
        foreach ((new CompareFilesystems(new Filesystem($backup), new Filesystem($source)))->compare() as $file) {
            $files[] = $file;
        }

        $this->assertCount(1, $files);
        $this->assertCount(1, $files[0]->getErrors());
        $this->assertSame(File::SOURCE_FILE_DOES_NOT_EXISTS_IN_BACKUP, $files[0]->getErrors()[0]);
    }

    /**
     * @test
     */
    public function will_compare_backup_is_smaller_than_source_file(): void
    {
        $path = 'temp1/file.txt';
        $content = 'abcdefgh';
        $config = (new Config())->set('timestamp', time());

        $source = new MemoryAdapter();
        $source->write($path, '', $config);
        $backup = new MemoryAdapter();
        $backup->write($path, $content, $config);

        $files = [];
        foreach ((new CompareFilesystems(new Filesystem($backup), new Filesystem($source)))->compare() as $file) {
            $files[] = $file;
        }

        $this->assertCount(1, $files);
        $this->assertCount(1, $files[0]->getErrors());
        $this->assertSame(File::SOURCE_FILE_IS_SMALLER_THAN_BACKUP, $files[0]->getErrors()[0]);
    }

    /**
     * @test
     */
    public function will_compare_backup_is_larger_than_source_file(): void
    {
        $path = 'temp1/file.txt';
        $content = 'abcdefgh';
        $config = (new Config())->set('timestamp', time());

        $source = new MemoryAdapter();
        $source->write($path, $content, $config);
        $backup = new MemoryAdapter();
        $backup->write($path, '', $config);

        $files = [];
        foreach ((new CompareFilesystems(new Filesystem($backup), new Filesystem($source)))->compare() as $file) {
            $files[] = $file;
        }

        $this->assertCount(1, $files);
        $this->assertCount(1, $files[0]->getErrors());
        $this->assertSame(File::SOURCE_FILE_IS_LARGER_THAN_BACKUP, $files[0]->getErrors()[0]);
    }

    /**
     * @test
     */
    public function will_compare_backup_file_is_older_than_source_file(): void
    {
        $path = 'temp1/file.txt';
        $content = 'abcdefgh';
        $config = (new Config())->set('timestamp', time());
        $sourceConfig = clone $config;
        $backupConfig = clone $config;

        $source = new MemoryAdapter();
        $source->write($path, $content, $sourceConfig);
        $backup = new MemoryAdapter();
        $backup->write($path, $content, $backupConfig->set('timestamp', time() + 100));

        $files = [];
        foreach ((new CompareFilesystems(new Filesystem($backup), new Filesystem($source)))->compare() as $file) {
            $files[] = $file;
        }

        $this->assertCount(1, $files);
        $this->assertCount(1, $files[0]->getErrors());
        $this->assertSame(File::SOURCE_FILE_IS_OLDER_THAN_BACUP, $files[0]->getErrors()[0]);
    }

    /**
     * @test
     */
    public function will_compare_backup_file_is_newer_than_source_file(): void
    {
        $path = 'temp1/file.txt';
        $content = 'abcdefgh';
        $config = (new Config())->set('timestamp', time());
        $sourceConfig = clone $config;
        $backupConfig = clone $config;

        $source = new MemoryAdapter();
        $source->write($path, $content, $sourceConfig->set('timestamp', time() + 100));
        $backup = new MemoryAdapter();
        $backup->write($path, $content, $backupConfig);

        $files = [];
        foreach ((new CompareFilesystems(new Filesystem($backup), new Filesystem($source)))->compare() as $file) {
            $files[] = $file;
        }

        $this->assertCount(1, $files);
        $this->assertCount(1, $files[0]->getErrors());
        $this->assertSame(File::SOURCE_FILE_IS_NEWER_THAN_BACKUP, $files[0]->getErrors()[0]);
    }

    /**
     * @test
     */
    public function will_get_multiple_errors(): void
    {
        $path = 'temp1/file.txt';
        $content = 'abcdefgh';
        $config = (new Config())->set('timestamp', time());
        $sourceConfig = clone $config;
        $backupConfig = clone $config;

        $source = new MemoryAdapter();
        $source->write($path, $content, $sourceConfig);
        $backup = new MemoryAdapter();
        $backup->write($path, '', $backupConfig->set('timestamp', time() + 100));

        $files = [];
        foreach ((new CompareFilesystems(new Filesystem($backup), new Filesystem($source)))->compare() as $file) {
            $files[] = $file;
        }

        $this->assertCount(1, $files);
        $this->assertCount(2, $files[0]->getErrors());
    }

    /**
     * @test
     */
    public function will_compare_multiple_files_in_multiple_directories(): void
    {
        $source = new MemoryAdapter();
        $source->write('temp/file1.txt', '', new Config([]));
        $source->write('temp/temp/file2.txt', '', new Config([]));
        $source->write('temp/temp/temp/file3.txt', '', new Config([]));
        $source->write('temp/file4.txt', '', new Config([]));
        $source->write('temp/file5.txt', '', new Config([]));

        $backup = new MemoryAdapter();

        $files = [];
        foreach ((new CompareFilesystems(new Filesystem($backup), new Filesystem($source)))->compare() as $file) {
            $files[] = $file;
        }

        $this->assertCount(5, $files);
        foreach ($files as $file) {
            $this->assertCount(1, $file->getErrors());
            $this->assertSame(File::SOURCE_FILE_DOES_NOT_EXISTS_IN_BACKUP, $file->getErrors()[0]);
        }
    }
}
