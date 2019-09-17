<?php

declare(strict_types=1);

namespace Lsv\BackupCompareFilesystemsTests;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Lsv\BackupCompareFilesystems\Compare;
use Lsv\BackupCompareFilesystems\Model\File;
use PHPUnit\Framework\TestCase;

class CompareTest extends TestCase
{
    /**
     * @var Compare
     */
    private $compare;

    /**
     * @test
     */
    public function can_compare_directories(): void
    {
        $files = $this->compare->compare();
        $this->assertCount(5, $files);
        foreach ($files as $file) {
            switch ($file->getFilename()) {
                case 'file3.txt':
                    $this->assertSame('temp1/temp2/file3.txt', $file->getPath());
                    $this->assertCount(2, $file->getErrors());
                    $this->assertSame(File::SOURCE_FILE_IS_OLDER_THAN_BACUP, $file->getErrors()[0]);
                    $this->assertSame(File::SOURCE_FILE_IS_SMALLER_THAN_BACKUP, $file->getErrors()[1]);
                    break;
                case 'file2.txt':
                    $this->assertCount(2, $file->getErrors());
                    $this->assertSame(File::SOURCE_FILE_IS_OLDER_THAN_BACUP, $file->getErrors()[0]);
                    $this->assertSame(File::SOURCE_FILE_IS_BIGGER_THAN_BACKUP, $file->getErrors()[1]);
                    break;
                case 'file1.txt':
                    $this->assertCount(1, $file->getErrors());
                    $this->assertSame(File::SOURCE_FILE_IS_NEWER_THAN_BACKUP, $file->getErrors()[0]);
                    break;
                case 'matrixrates.csv':
                    $this->assertCount(0, $file->getErrors());
                    break;
                case 'tablerates.csv':
                    $this->assertCount(1, $file->getErrors());
                    $this->assertSame(File::SOURCE_FILE_DOES_NOT_EXISTS_IN_BACKUP, $file->getErrors()[0]);
                    break;
            }
        }
    }

    protected function setUp(): void
    {
        $backup = new Local(__DIR__.'/files/backup');
        $backupFs = new Filesystem($backup);

        $source = new Local(__DIR__.'/files/source');
        $sourceFs = new Filesystem($source);

        $this->compare = new Compare($backupFs, $sourceFs);
    }
}
