<?php

namespace Foldergallery;

use ApprovalTests\Approvals;
use Foldergallery\Infra\View;
use PHPUnit\Framework\TestCase;
use Plib\FakeSystemChecker;

class InfoControllerTest extends TestCase
{
    public function testRendersPluginInfo(): void
    {
        $plugin_tx = XH_includeVar("./languages/en.php", "plugin_tx");
        $view = new View("./views/", $plugin_tx["foldergallery"]);
        $sut = new InfoController("./plugins/foldergallery/", new FakeSystemChecker(), $view);
        $response = $sut();
        $this->assertEquals("Foldergallery 1.0beta1", $response->title());
        Approvals::verifyHtml($response->output());
    }
}
