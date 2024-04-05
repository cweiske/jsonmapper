<?php declare(strict_types=1);

use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Symfony\Symfony64\Rector\Class_\ChangeRouteAttributeFromAnnotationSubnamespaceRector;
use Rector\ValueObject\PhpVersion;

return static function (RectorConfig $rectorConfig): void
{
    $rectorConfig->PHPVersion(PhpVersion::PHP_83);
    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');
    $rectorConfig->cacheDirectory('./var/cache/rector');
    $rectorConfig->paths(
        [
            __DIR__ . '/src',
        ]
    );

    $rectorConfig->symfonyContainerXml(__DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml');
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_80,
        LevelSetList::UP_TO_PHP_81,
        LevelSetList::UP_TO_PHP_82,
        LevelSetList::UP_TO_PHP_83,
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
        SetList::CODE_QUALITY,
        SetList::CODING_STYLE,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
        SymfonySetList::SYMFONY_62,
        SymfonySetList::SYMFONY_63,
        SymfonySetList::SYMFONY_64,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]);

    $rectorConfig->skip([
        __DIR__.'/src/Controller/Testseite',
        __DIR__.'/src/Extension/AgGrid',
        __DIR__.'/src/View',
        AddOverrideAttributeToOverriddenMethodsRector::class,
        ExplicitBoolCompareRector::class,
        FinalizeClassesWithoutChildrenRector::class => [
            __DIR__.'/src/Entity',
        ],
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
        ChangeRouteAttributeFromAnnotationSubnamespaceRector::class,
        ]);
};