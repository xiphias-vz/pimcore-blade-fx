<?php

namespace Xiphias\Bundle\PimcoreBladeFx\Service\Builder;

use Xiphias\Bundle\PimcoreBladeFx\DTO\PaginationTransfer;
use Xiphias\BladeFxApi\DTO\BladeFxCategoryTransfer;

interface GridBuilderInterface
{
    /**
     * @param array<BladeFxCategoryTransfer> $categoryTransfers
     * @return array
     */
    public function createCategoryTree(array $categoryTransfers): array;

    /**
     * @param PaginationTransfer $paginationTransfer
     * @return array
     */
    public function paginateResults(PaginationTransfer $paginationTransfer): array;
}
