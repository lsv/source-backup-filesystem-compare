<?php

declare(strict_types=1);

namespace Lsv\BackupCompareFilesystems\Model;

use DateTime;

class File
{
    public const SOURCE_FILE_DOES_NOT_EXISTS_IN_BACKUP = 1;

    // Sizes
    public const SOURCE_FILE_IS_SMALLER_THAN_BACKUP = 2;
    public const SOURCE_FILE_IS_LARGER_THAN_BACKUP = 3;
    // Timestamps
    public const SOURCE_FILE_IS_OLDER_THAN_BACUP = 4;
    public const SOURCE_FILE_IS_NEWER_THAN_BACKUP = 5;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var DateTime|null
     */
    private $backupTimestamp;

    /**
     * @var DateTime
     */
    private $sourceTimestamp;

    /**
     * @var int|null
     */
    private $backupSize;

    /**
     * @var int
     */
    private $sourceSize;

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getBackupTimestamp(): ?DateTime
    {
        return $this->backupTimestamp;
    }

    public function setBackupTimestamp(?DateTime $backupTimestamp): self
    {
        $this->backupTimestamp = $backupTimestamp;

        return $this;
    }

    public function getSourceTimestamp(): DateTime
    {
        return $this->sourceTimestamp;
    }

    public function setSourceTimestamp(DateTime $sourceTimestamp): self
    {
        $this->sourceTimestamp = $sourceTimestamp;

        return $this;
    }

    public function getBackupSize(): ?int
    {
        return $this->backupSize;
    }

    public function setBackupSize(?int $backupSize): self
    {
        $this->backupSize = $backupSize;

        return $this;
    }

    public function getSourceSize(): int
    {
        return $this->sourceSize;
    }

    public function setSourceSize(int $sourceSize): self
    {
        $this->sourceSize = $sourceSize;

        return $this;
    }

    public function getErrors(): array
    {
        $errors = [];
        if (null === $this->getBackupTimestamp() || null === $this->getBackupSize()) {
            $errors[] = self::SOURCE_FILE_DOES_NOT_EXISTS_IN_BACKUP;

            return $errors;
        }

        if ($this->getBackupTimestamp() < $this->getSourceTimestamp()) {
            $errors[] = self::SOURCE_FILE_IS_NEWER_THAN_BACKUP;
        }

        if ($this->getBackupTimestamp() > $this->getSourceTimestamp()) {
            $errors[] = self::SOURCE_FILE_IS_OLDER_THAN_BACUP;
        }

        if ($this->getBackupSize() < $this->getSourceSize()) {
            $errors[] = self::SOURCE_FILE_IS_LARGER_THAN_BACKUP;
        }

        if ($this->getBackupSize() > $this->getSourceSize()) {
            $errors[] = self::SOURCE_FILE_IS_SMALLER_THAN_BACKUP;
        }

        return $errors;
    }
}
