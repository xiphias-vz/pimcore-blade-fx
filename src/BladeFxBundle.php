<?php

namespace Xiphias\Bundle\PimcoreBladeFx;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;

class BladeFxBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface
{
    public function getJsPaths(): array
    {
        return [
            '/bundles/bladefx/js/blade-fx.js',
            '/bundles/bladefx/js/config.js',
        ];
    }

    public function getCssPaths(): array
    {
        return [
            '/bundles/bladefx/css/icons.css',
            '/bundles/bladefx/css/blade-fx.css',
        ];
    }

    public function getEditmodeJsPaths(): array
    {
        return $this->getJsPaths();
    }

    public function getEditmodeCssPaths(): array
    {
        return $this->getCssPaths();
    }
}
