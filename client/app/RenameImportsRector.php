<?php

declare(strict_types=1);

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * This is not ideally located on the filesystem, but putting it into the src directory causes phpstan to
 * get upset as we're not installing rector via composer. At the moment, we can't add rector v2 to composer, as it
 * requires a different version of phpstan which is incompatible with what we're currently using.
 *
 * Once we upgrade to a later phpstan, we could include rector as a dev dependency.
 *
 * Example (in your rector.php):
 *
 * return RectorConfig::configure()->withConfiguredRule(RenameImportsRector::class, [
 *     'Sensio\Bundle\FrameworkExtraBundle\Configuration\Template' => 'Symfony\Bridge\Twig\Attribute\Template'
 * ]);
 */
class RenameImportsRector extends AbstractRector implements ConfigurableRectorInterface
{
    private array $classNameChanges = [];

    public function getNodeTypes(): array
    {
        return [Namespace_::class];
    }

    public function refactor(Node $node): ?Node
    {
        foreach ($node->stmts as $stmt) {
            if (!$stmt instanceof Use_) {
                continue;
            }
            if (count($stmt->uses) !== 1) {
                continue;
            }
            if (!isset($stmt->uses[0])) {
                continue;
            }

            $currentName = $stmt->uses[0]->name->toString();
            foreach ($this->classNameChanges as $oldName => $newName) {
                if ($currentName === $oldName) {
                    $stmt->uses[0]->name = new Name($newName);
                }
            }
        }

        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change name of imported package to new value; only affects `use` statements',
            []
        );
    }

    /**
     * @param array $configuration ['old_class' => 'new_class', ...]
     * @return void
     */
    public function configure(array $configuration): void
    {
        $this->classNameChanges = $configuration;
    }
}

return RenameImportsRector::class;
