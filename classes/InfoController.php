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

use stdClass;

class InfoController
{
    /** @var SystemChecker */
    private $systemChecker;

    public function __construct()
    {
        $this->systemChecker = new SystemChecker();
    }

    /**
     * @return void
     */
    public function defaultAction()
    {
        global $pth, $plugin_tx;

        $view = new View($pth["folder"]["plugins"] . "foldergallery/views/", $plugin_tx["foldergallery"]);
        echo $view->render("info", [
            'logo' => "{$pth['folder']['plugins']}foldergallery/foldergallery.png",
            'version' => Plugin::VERSION,
            'checks' => $this->checks(),
        ]);
    }

    /** @return list<array{state:string,label:string,stateLabel:string}> */
    private function checks(): array
    {
        global $pth;

        return [
            $this->checkPhpVersion("5.4.0"),
            $this->checkExtension("gd"),
            $this->checkExtension("json"),
            $this->checkXhVersion("1.6.3"),
            $this->checkPlugin("jquery"),
            $this->checkWritability("{$pth['folder']['plugins']}foldergallery/cache/"),
            $this->checkWritability("{$pth['folder']['plugins']}foldergallery/config/"),
            $this->checkWritability("{$pth['folder']['plugins']}foldergallery/css/"),
            $this->checkWritability("{$pth['folder']['plugins']}foldergallery/languages/"),
        ];
    }

    /** @return array{state:string,label:string,stateLabel:string} */
    private function checkPhpVersion(string $version): stdClass
    {
        global $plugin_tx;

        $state = $this->systemChecker->checkVersion(PHP_VERSION, $version) ? "success" : "fail";
        return (object) [
            "state" => $state,
            "label" => sprintf($plugin_tx["foldergallery"]["syscheck_phpversion"], $version),
            "stateLabel" => $plugin_tx["foldergallery"]["syscheck_$state"],
        ];
    }

    /** @return array{state:string,label:string,stateLabel:string} */
    private function checkExtension(string $name): stdClass
    {
        global $plugin_tx;

        $state = $this->systemChecker->checkExtension($name) ? "success" : "fail";
        return (object) [
            "state" => $state,
            "label" => sprintf($plugin_tx["foldergallery"]["syscheck_extension"], $name),
            "stateLabel" => $plugin_tx["foldergallery"]["syscheck_$state"],
        ];
    }

    /** @return array{state:string,label:string,stateLabel:string} */
    private function checkXhVersion(string $version): stdClass
    {
        global $plugin_tx;

        $state = $this->systemChecker->checkVersion(CMSIMPLE_XH_VERSION, "CMSimple_XH $version") ? "success" : "fail";
        return (object) [
            "state" => $state,
            "label" => sprintf($plugin_tx["foldergallery"]["syscheck_xhversion"], $version),
            "stateLabel" => $plugin_tx["foldergallery"]["syscheck_$state"],
        ];
    }

    private function checkPlugin(string $name): stdClass
    {
        global $plugin_tx;

        $state = $this->systemChecker->checkPlugin($name) ? "success" : "fail";
        return (object) [
            "state" => $state,
            "label" => sprintf($plugin_tx["foldergallery"]["syscheck_plugin"], $name),
            "stateLabel" => $plugin_tx["foldergallery"]["syscheck_$state"],
        ];
    }

    /** @return array{state:string,label:string,stateLabel:string} */
    private function checkWritability(string $folder): stdClass
    {
        global $plugin_tx;

        $state = $this->systemChecker->checkWritability($folder) ? "success" : "warning";
        return (object) [
            "state" => $state,
            "label" => sprintf($plugin_tx["foldergallery"]["syscheck_writable"], $folder),
            "stateLabel" => $plugin_tx["foldergallery"]["syscheck_$state"],
        ];
    }
}
