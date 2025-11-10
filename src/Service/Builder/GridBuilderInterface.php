<?php

namespace Xiphias\Bundle\PimcoreBladeFx\Service\Builder;

use Xiphias\Bundle\PimcoreBladeFx\DTO\PaginationTransfer;
use Xiphias\BladeFxApi\DTO\BladeFxCategoryTransfer;

interface GridBuilderInterface
{
    /**
     * @param array<BladeFxCategoryTransfer> $categoryTransfers
     * @return array<string, bool|int|string|null>
     */
    public function createCategoryTree(array $categoryTransfers): array;

    /**
     * @param PaginationTransfer $paginationTransfer
     * @return array<mixed>
     */
    public function paginateResults(PaginationTransfer $paginationTransfer): array;
}
