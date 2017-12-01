<?php

/**
 * Copyright 2017 Christoph M. Becker
 *
 * This file is part of Foldergallery_XH.
 *
 * Foldergallery_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Foldergallery_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foldergallery_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Foldergallery;

use Pfw\SystemCheckService;
use Pfw\View\View;

class Plugin
{
    const VERSION = '@PLUGIN_VERSION@';

    public function run()
    {
        global $admin, $action, $o;

        if (XH_ADM) {
            XH_registerStandardPluginMenuItems(false);
            if (XH_wantsPluginAdministration('foldergallery')) {
                $o .= print_plugin_admin('off');
                switch ($admin) {
                    case '':
                        $o .= $this->renderInfo();
                        break;
                    default:
                        $o .= plugin_admin_common($action, $admin, 'foldergallery');
                }
            }
        }
    }

    /**
     * @return string
     */
    private function renderInfo()
    {
        global $pth;

        ob_start();
        (new View('foldergallery'))
            ->template('info')
            ->data([
                'logo' => "{$pth['folder']['plugins']}foldergallery/foldergallery.png",
                'version' => self::VERSION,
                'checks' => (new SystemCheckService)
                    ->minPhpVersion('5.4.0')
                    ->extension('gd')
                    ->extension('json')
                    ->minXhVersion('1.6.3')
                    ->plugin('pfw')
                    ->plugin('jquery')
                    ->writable("{$pth['folder']['plugins']}foldergallery/cache/")
                    ->writable("{$pth['folder']['plugins']}foldergallery/config/")
                    ->writable("{$pth['folder']['plugins']}foldergallery/css/")
                    ->writable("{$pth['folder']['plugins']}foldergallery/languages/")
                    ->getChecks()
            ])
            ->render();
        return ob_get_clean();
    }
}
