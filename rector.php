<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\ClassMethod\ReturnTypeFromStrictScalarReturnExprRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\PHPUnit\Rector\Class_\AddSeeTestAnnotationRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_71,
        SymfonyLevelSetList::UP_TO_SYMFONY_44,
        SymfonyLevelSetList::UP_TO_SYMFONY_62,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]);
    $rectorConfig->rules([
        ReturnTypeFromStrictNativeCallRector::class,
        ReturnTypeFromStrictScalarReturnExprRector::class,
     ]);
    $rectorConfig->phpVersion(PhpVersion::PHP_71);
    $rectorConfig->importShortClasses(false);
    $rectorConfig->importNames();
    $rectorConfig->bootstrapFiles([
          __DIR__ . '/vendor/autoload.php',
      ]);
    $rectorConfig->parallel();
    $rectorConfig->paths([__DIR__]);
    $rectorConfig->skip([
        // Path
        __DIR__ . '/.github',
        __DIR__ . '/DependencyInjection/Configuration.php',
        __DIR__ . '/vendor',

        // Rules
        AddSeeTestAnnotationRector::class,
        JsonThrowOnErrorRector::class,
    ]);
};
