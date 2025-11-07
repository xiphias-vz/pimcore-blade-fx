<?php

declare(strict_types=1);

namespace Xiphias\Bundle\PimcoreBladeFx\Controller;

use Xiphias\Bundle\PimcoreBladeFx\BladeFxConstants;
use Xiphias\Bundle\PimcoreBladeFx\DTO\PaginationTransfer;
use Xiphias\Bundle\PimcoreBladeFx\Service\Builder\GridBuilderInterface;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Xiphias\BladeFxApi\BladeFxApiClient;
use Xiphias\BladeFxApi\DTO\BladeFxGetCategoriesListRequestTransfer;
use Xiphias\BladeFxApi\DTO\BladeFxGetReportParamFormRequestTransfer;
use Xiphias\BladeFxApi\DTO\BladeFxGetReportsListRequestTransfer;
use Xiphias\BladeFxApi\DTO\BladeFxSetFavoriteReportRequestTransfer;

/**
 * @internal
 */
#[Route('/admin/bladefx/config')]
class ConfigController extends UserAwareController
{
    use JsonHelperTrait;

    public const string CONFIG_NAME = 'plugin_bladefx_config';

    /**
     * @param string $bladeFxRootUrl
     * @param GridBuilderInterface $gridBuilder
     */
    public function __construct(
        protected string $bladeFxRootUrl,
        protected GridBuilderInterface $gridBuilder
    )
    {
    }

    /**
     * @param BladeFxApiClient $apiClient
     * @return JsonResponse
     */
    #[Route('/category-list', name: 'pimcore_bundle_bladefx_category_list')]
    public function categoryListAction(BladeFxApiClient $apiClient): JsonResponse
    {
        $this->checkPermission(self::CONFIG_NAME);

        $requestTransfer = new BladeFxGetCategoriesListRequestTransfer();
        $requestTransfer->setReturnType(BladeFxConstants::REQUEST_RETURN_TYPE_JSON);

        $responseTransfer = $apiClient->sendGetCategoriesListRequest($requestTransfer);
        $categoriesList = $responseTransfer->getCategoriesList();

        $categoryTree = $this->gridBuilder->createCategoryTree($categoriesList);

        return $this->json($categoryTree);
    }

    /**
     * @param Request $request
     * @param BladeFxApiClient $apiClient
     * @return JsonResponse
     */
    #[Route('/report-list', name: 'pimcore_bundle_bladefx_report_list')]
    public function reportList(Request $request, BladeFxApiClient $apiClient): JsonResponse
    {
        $this->checkPermission(self::CONFIG_NAME);

        $requestTransfer = new BladeFxGetReportsListRequestTransfer();
        $requestTransfer->setReturnType(BladeFxConstants::REQUEST_RETURN_TYPE_JSON);
        $requestTransfer->setCatId((int)$request->query->get(BladeFxConstants::REQUEST_CATEGORY_ID));

        $responseTransfer = $apiClient->sendGetReportsListRequest($requestTransfer);
        $reportsList = $responseTransfer->getReportsList();
        $total = count($reportsList);

        $paginationTransfer = $this->createPaginationTransfer($request, $reportsList);
        $paginatedResults = $this->gridBuilder->paginateResults($paginationTransfer);

        $responseTransfer->setReportsList($paginatedResults);
        $paginatedResultsArr = $responseTransfer->toArray();
        $paginatedResultsArr[BladeFxConstants::RESPONSE_TOTAL] = $total;

        return $this->json($paginatedResultsArr);
    }

    /**
     * @param Request $request
     * @param array $data
     * @return PaginationTransfer
     */
    protected function createPaginationTransfer(Request $request, array $data): PaginationTransfer
    {
        $paginationTransfer = new PaginationTransfer();
        $page = (int)$request->query->get('page');
        $limit = (int)$request->query->get('limit');
        $paginationTransfer->setOffset(($page - 1) * $limit);
        $paginationTransfer->setLimit($limit);
        $paginationTransfer->setData($data);
        return $paginationTransfer;
    }

    /**
     * @param Request $request
     * @param BladeFxApiClient $apiClient
     * @return JsonResponse
     */
    #[Route('/preview-report', name: 'pimcore_bundle_bladefx_preview-report')]
    public function previewReport(Request $request, BladeFxApiClient $apiClient): JsonResponse
    {
        $requestTransfer = new BladeFxGetReportParamFormRequestTransfer();
        $requestTransfer->setRootUrl($this->bladeFxRootUrl);
        $requestTransfer->setReportId((int)$request->query->get(BladeFxConstants::REQUEST_REPORT_ID));

        $previewUrl = $apiClient->sendGetReportParamFormRequest($requestTransfer)->getIframeUrl();

        return $this->json([BladeFxConstants::RESPONSE_IFRAME_URL => $previewUrl]);
    }

    #[Route('/favorite-report', name: 'pimcore_bundle_bladefx_favorite-report')]
    public function favoriteReport(Request $request, BladeFxApiClient $apiClient): JsonResponse
    {
        $requestTransfer = new BladeFxSetFavoriteReportRequestTransfer();
        $requestTransfer->setRepId((int)$request->query->get(BladeFxConstants::REQUEST_REPORT_ID));
        $authenticationResponseTransfer = $apiClient->sendAuthenticateUserRequest(null);
        $requestTransfer->setUserId($authenticationResponseTransfer->getIdUser());

        $response = $apiClient->sendSetFavoriteReportRequest($requestTransfer);

        return $this->json($response);
    }
}
