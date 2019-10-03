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
    private $targetTimestamp;

    /**
     * @var DateTime
     */
    private $sourceTimestamp;

    /**
     * @var int|null
     */
    private $targetSize;

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

    public function getTargetTimestamp(): ?DateTime
    {
        return $this->targetTimestamp;
    }

    public function setTargetTimestamp(?DateTime $targetTimestamp): self
    {
        $this->targetTimestamp = $targetTimestamp;

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

    public function getTargetSize(): ?int
    {
        return $this->targetSize;
    }

    public function setTargetSize($targetSize): self
    {
        if ($targetSize) {
            $this->targetSize = (int) $targetSize;
        }

        return $this;
    }

    public function getSourceSize(): int
    {
        return $this->sourceSize;
    }

    public function setSourceSize($sourceSize): self
    {
        $this->sourceSize = (int) $sourceSize;

        return $this;
    }

    public function getErrors(): array
    {
        $errors = [];
        if (null === $this->getTargetTimestamp() || null === $this->getTargetSize()) {
            $errors[] = self::SOURCE_FILE_DOES_NOT_EXISTS_IN_BACKUP;

            return $errors;
        }

        if ($this->getTargetTimestamp() < $this->getSourceTimestamp()) {
            $errors[] = self::SOURCE_FILE_IS_NEWER_THAN_BACKUP;
        }

        if ($this->getTargetTimestamp() > $this->getSourceTimestamp()) {
            $errors[] = self::SOURCE_FILE_IS_OLDER_THAN_BACUP;
        }

        if ($this->getTargetSize() < $this->getSourceSize()) {
            $errors[] = self::SOURCE_FILE_IS_LARGER_THAN_BACKUP;
        }

        if ($this->getTargetSize() > $this->getSourceSize()) {
            $errors[] = self::SOURCE_FILE_IS_SMALLER_THAN_BACKUP;
        }

        return $errors;
    }
}
