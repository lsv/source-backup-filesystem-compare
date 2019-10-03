<?php

declare(strict_types=1);

namespace Lsv\BackupCompareFilesystems;

use DateTime;
use Generator;
use League\Flysystem\FilesystemInterface;
use Lsv\BackupCompareFilesystems\Model\File;

class CompareFilesystems
{
    /**
     * @var FilesystemInterface
     */
    private $backupFilesystem;
    /**
     * @var FilesystemInterface
     */
    private $sourceFilesystem;

    public function __construct(FilesystemInterface $backupFilesystem, FilesystemInterface $sourceFilesystem)
    {
        $this->backupFilesystem = $backupFilesystem;
        $this->sourceFilesystem = $sourceFilesystem;
    }

    /**
     * @return File[]|Generator
     */
    public function compare(): Generator
    {
        $sources = $this->sourceFilesystem->listContents('.', true);
        foreach ($sources as $source) {
            switch ($source['type']) {
                case 'dir':
                    break;
                case 'file':
                    yield $this->readFile($source);
                    break;
            }
        }
    }

    protected function readFile(array $sourceFile): File
    {
        $file = (new File())
            ->setPath($sourceFile['path'])
            ->setFilename($sourceFile['basename'])
            ->setSourceSize($sourceFile['size'])
            ->setSourceTimestamp($this->setTimestamp($sourceFile['timestamp']));

        if ($this->backupFilesystem->has($sourceFile['path'])) {
            $metadata = $this->backupFilesystem->getMetadata($sourceFile['path']);
            $file
                ->setBackupTimestamp($this->setTimestamp($metadata['timestamp']))
                ->setBackupSize($metadata['size']);
        }

        return $file;
    }

    private function setTimestamp($timestamp): DateTime
    {
        return (new DateTime())->setTimestamp($timestamp);
    }
}
