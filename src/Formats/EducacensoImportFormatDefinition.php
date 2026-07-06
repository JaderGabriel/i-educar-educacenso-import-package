<?php

declare(strict_types=1);

namespace iEducar\Packages\BuritiEducacensoImport\Formats;

final class EducacensoImportFormatDefinition
{
  public function __construct(
    public readonly int $year,
    public readonly string $slug,
    public readonly string $title,
    public readonly string $description,
    public readonly string $mimeHint,
    public readonly array $extensions,
    public readonly int $process,
    public readonly string $parserType,
  ) {}
}
