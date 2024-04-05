<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->cacheDirectory('/var/cache/ecs');
    $ecsConfig->parallel();

    // folders
    $ecsConfig->paths([
        'src',
        'tests',
    ]);

    // this way you add a single rule
    $ecsConfig->rules([
        NoUnusedImportsFixer::class,
    ]);

    // this way you can add sets - group of rules
    $ecsConfig->sets([
        SetList::ARRAY,
        SetList::CLEAN_CODE,
        SetList::COMMENTS,
        SetList::COMMON,
        SetList::CONTROL_STRUCTURES,
        SetList::DOCBLOCK,
        SetList::NAMESPACES,
        SetList::PSR_12,
        SetList::SPACES,
        SetList::STRICT,
    ]);

    // skip paths/files/rules
    $ecsConfig->skip([
        NotOperatorWithSuccessorSpaceFixer::class,
        'tests/support/JsonMapperTest/Simple.php',
        'tests/support/namespacetest/UnitData.php',
    ]);

    // psr12
    $ecsConfig->ruleWithConfiguration(OrderedImportsFixer::class, ['imports_order' => ['class', 'function', 'const']]);
    $ecsConfig->ruleWithConfiguration(DeclareEqualNormalizeFixer::class, ['space' => 'none']);
    $ecsConfig->ruleWithConfiguration(WhitespaceAfterCommaInArrayFixer::class, ['ensure_single_space'=> \true]);
    $ecsConfig->ruleWithConfiguration(VisibilityRequiredFixer::class, ['elements' => ['const', 'method', 'property']]);
    $ecsConfig->ruleWithConfiguration(SingleClassElementPerStatementFixer::class, ['elements' => ['property']]);
    $ecsConfig->ruleWithConfiguration(ConcatSpaceFixer::class, ['spacing' => 'one']);
};


