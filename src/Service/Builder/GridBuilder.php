<?php

declare(strict_types=1);

namespace Xiphias\Bundle\BladeFxBundle\Service\Builder;

use Xiphias\Bundle\BladeFxBundle\DTO\PaginationTransfer;
use Symfony\Contracts\Translation\TranslatorInterface;
use Xiphias\BladeFxApi\DTO\BladeFxCategoryTransfer;

class GridBuilder implements GridBuilderInterface
{
    /**
     * @var string
     */
    public const KEY_CATEGORY_PARENT_ID = 'idParent';

    /**
     * @var string
     */
    public const KEY_VALUE = 'value';

    /**
     * @var string
     */
    public const KEY_CHILDREN = 'children';

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(protected TranslatorInterface $translator)
    {
    }

    /**
     * @param array<BladeFxCategoryTransfer> $categoryTransfers
     * @return array<mixed>
     */
    public function createCategoryTree(array $categoryTransfers): array
    {
        $categories = $this->assembleCategoryTree($categoryTransfers);
        $children = $this->createCategoryNodes($categories);

        return [[
            'id' => null,
            'text' => $this->translator->trans('plugin_blade_fx_all_categories', [], 'admin'),
            'type' => 'text',
            'expandable' => $children !== [],
            'expanded' => false,
            'leaf' => $children === [],
            'children' => $children,
        ]];
    }

    /**
     * @param array $categories
     *
     * @return array
     */
    public function assembleCategoryTree(array $categories): array
    {
        $categoryTree = [];
        $childrenByParent = [];

        foreach ($categories as $category) {
            if (!is_array($category)) {
                $category = $category->toArray();
            }

            $categoryParentId = (int) ($category[static::KEY_CATEGORY_PARENT_ID] ?? 0);
            $categoryId = $category['id'];
            $categoryTree[$categoryId] = [
                static::KEY_CATEGORY_PARENT_ID => $categoryParentId,
                static::KEY_VALUE => $category,
            ];
        }

        foreach ($categoryTree as $categoryId => $category) {
            $parentId = $category[static::KEY_CATEGORY_PARENT_ID];
            if ($parentId !== 0 && $parentId !== $categoryId && isset($categoryTree[$parentId])) {
                $childrenByParent[$parentId][] = $categoryId;
            }
        }

        $buildBranch = function (int $categoryId, array $ancestors = []) use (
            &$buildBranch,
            $categoryTree,
            $childrenByParent
        ): array {
            $branch = $categoryTree[$categoryId];
            $ancestors[$categoryId] = true;

            foreach ($childrenByParent[$categoryId] ?? [] as $childId) {
                if (!isset($ancestors[$childId])) {
                    $branch[static::KEY_CHILDREN][] = $buildBranch($childId, $ancestors);
                }
            }

            return $branch;
        };

        $tree = [];
        foreach ($categoryTree as $categoryId => $category) {
            $parentId = $category[static::KEY_CATEGORY_PARENT_ID];
            if ($parentId === 0 || $parentId === $categoryId || !isset($categoryTree[$parentId])) {
                $tree[] = $buildBranch($categoryId);
            }
        }

        return $tree;
    }

    /**
     * @param array<mixed> $categories
     * @return array<mixed>
     */
    protected function createCategoryNodes(array $categories): array
    {
        $nodes = [];

        foreach ($categories as $category) {
            $value = $category[static::KEY_VALUE];
            $children = $this->createCategoryNodes($category[static::KEY_CHILDREN] ?? []);

            $node = [
                'id' => $value['id'],
                'text' => htmlspecialchars((string) $value['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                'type' => 'text',
                'expandable' => $children !== [],
                'leaf' => $children === [],
            ];

            if ($children !== []) {
                $node[static::KEY_CHILDREN] = $children;
            }

            $nodes[] = $node;
        }

        return $nodes;
    }

    /**
     * @param PaginationTransfer $paginationTransfer
     * @return array<mixed>
     */
    public function paginateResults(PaginationTransfer $paginationTransfer): array
    {
        return array_slice($paginationTransfer->getData(), $paginationTransfer->getOffset(), $paginationTransfer->getLimit());
    }
}
