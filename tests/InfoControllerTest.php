<?php

namespace Foldergallery;

use ApprovalTests\Approvals;
use Foldergallery\Infra\SystemChecker;
use Foldergallery\Infra\View;
use PHPUnit\Framework\TestCase;

class InfoControllerTest extends TestCase
{
    public function testRendersPluginInfo(): void
    {
        $systemChecker = $this->createMock(SystemChecker::class);
        $systemChecker->method("checkVersion")->willReturn(false);
        $systemChecker->method("checkExtension")->willReturn(false);
        $systemChecker->method("checkPlugin")->willReturn(false);
        $systemChecker->method("checkWritability")->willReturn(false);
        $plugin_tx = XH_includeVar("./languages/en.php", "plugin_tx");
        $view = new View("./views/", $plugin_tx["foldergallery"]);
        $sut = new InfoController("./plugins/foldergallery/", $systemChecker, $view);
        $response = $sut();
        $this->assertEquals("Foldergallery 1.0beta1", $response->title());
        Approvals::verifyHtml($response->output());
    }
}
