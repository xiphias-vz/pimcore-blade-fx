<?php

namespace Xiphias\Bundle\BladeFxBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\PimcoreBundleAdminClassicInterface;

class PimcoreBladeFxBundle extends AbstractPimcoreBundle implements PimcoreBundleAdminClassicInterface
{
    public function getJsPaths(): array
    {
        return [
            '/bundles/pimcorebladefx/js/blade-fx.js',
            '/bundles/pimcorebladefx/js/config.js',
        ];
    }

    public function getCssPaths(): array
    {
        return [
            '/bundles/pimcorebladefx/css/icons.css',
            '/bundles/pimcorebladefx/css/blade-fx.css',
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
