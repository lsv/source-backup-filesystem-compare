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
    private $sourceFilesystem;
    /**
     * @var FilesystemInterface
     */
    private $targetFilesystem;

    public function __construct(FilesystemInterface $sourceFilesystem, FilesystemInterface $targetFilesystem)
    {
        $this->sourceFilesystem = $sourceFilesystem;
        $this->targetFilesystem = $targetFilesystem;
    }

    /**
     * @return File[]|Generator
     */
    public function compare(): Generator
    {
        $sources = $this->targetFilesystem->listContents('.', true);
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

        if ($this->sourceFilesystem->has($sourceFile['path'])) {
            $metadata = $this->sourceFilesystem->getMetadata($sourceFile['path']);
            $file
                ->setTargetTimestamp($this->setTimestamp($metadata['timestamp']))
                ->setTargetSize($metadata['size']);
        }

        return $file;
    }

    private function setTimestamp($timestamp): DateTime
    {
        return (new DateTime())->setTimestamp($timestamp);
    }
}
