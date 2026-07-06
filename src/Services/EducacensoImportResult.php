<?php

declare(strict_types=1);

namespace iEducar\Packages\BuritiEducacensoImport\Services;

final class EducacensoImportResult
{
  public function __construct(
    public readonly int $year,
    public readonly int $schoolId,
    public readonly string $formatSlug,
    public readonly string $formatTitle,
    public readonly string $fileName,
    public readonly int $linesRead,
    public readonly int $imported,
    public readonly int $skipped,
    public readonly int $errors,
    /** @var list<array{linha: int|string, tipo: string, mensagem: string}> */
    public readonly array $messages,
    /** @var array<string, int> */
    public readonly array $countsByType,
  ) {}
}
