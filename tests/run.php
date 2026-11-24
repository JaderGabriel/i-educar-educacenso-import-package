#!/usr/bin/env php
<?php

declare(strict_types=1);

use iEducar\Packages\BuritiEducacensoImport\Formats\EducacensoImportFormatCatalog;

$root = dirname(__DIR__);
$autoload = dirname(__DIR__, 3).'/vendor/autoload.php';
if (is_file($autoload)) {
    require $autoload;
} else {
    // fallback mínimo: carregar classes do pacote
    require $root.'/src/Process.php';
    require $root.'/src/Formats/EducacensoImportFormatDefinition.php';
    require $root.'/src/Formats/EducacensoImportFormatCatalog.php';
}

$ok = 0;
$fail = 0;
$assert = static function (bool $c, string $m) use (&$ok, &$fail): void {
    echo ($c ? '  OK  ' : ' FAIL ').$m."\n";
    $c ? $ok++ : $fail++;
};

$anos = EducacensoImportFormatCatalog::supportedYears();
$assert(in_array(2025, $anos, true) && in_array(2026, $anos, true), 'anos 2025 e 2026 suportados');

$f25 = EducacensoImportFormatCatalog::formatsForYear(2025);
$f26 = EducacensoImportFormatCatalog::formatsForYear(2026);
$assert(count($f25) >= 2, '≥2 formatos em 2025');
$assert(count($f26) >= 1, '≥1 formato em 2026');

$mig = EducacensoImportFormatCatalog::find(2025, 'migracao-txt');
$assert($mig !== null && $mig->parserType === 'migracao_txt', 'layout migração TXT 2025');

$id = EducacensoImportFormatCatalog::find(2025, 'identificacao-retorno-txt');
$assert($id !== null, 'layout identificação retorno 2025');

echo "\nResultado: {$ok} ok, {$fail} falhas\n";
exit($fail > 0 ? 1 : 0);
