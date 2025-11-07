<?php

declare(strict_types=1);

namespace Xiphias\Bundle\PimcoreBladeFx\DTO;

use Xiphias\BladeFxApi\DTO\BladeFxReportTransfer;

class PaginationTransfer
{
    /**
     * @var int
     */
    protected int $offset;

    /**
     * @var int
     */
    protected int $limit;

    /**
     * @var array<BladeFxReportTransfer>
     */
    protected array $data;

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset(int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return array<BladeFxReportTransfer>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<BladeFxReportTransfer> $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
