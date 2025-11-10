<?php

declare(strict_types=1);

namespace Xiphias\Bundle\BladeFxBundle\Service\Builder;

use Xiphias\Bundle\BladeFxBundle\DTO\PaginationTransfer;
use Symfony\Contracts\Translation\TranslatorInterface;
use Xiphias\BladeFxApi\DTO\BladeFxCategoryTransfer;

class GridBuilder implements GridBuilderInterface
{
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
        $tree = [];

        $tree[] = [
            'id' => null,
            'text' => $this->translator->trans('plugin_blade_fx_all_categories', [], 'admin'),
            'type' => 'text',
            'expandable' => false,
            'leaf' => true
        ];

        foreach ($categoryTransfers as $categoryTransfer) {
            $treeElement = [
                'id' => $categoryTransfer->getCatId(),
                'text' => htmlspecialchars($categoryTransfer->getCatName()),
                'type' => 'text',
                'expandable' => false,
                'leaf' => true,
            ];
            $tree[] = $treeElement;
        }

        return $tree;
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
