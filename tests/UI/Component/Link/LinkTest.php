<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use ILIAS\UI\Implementation\Component as I;
use ILIAS\Data\LanguageTag;

/**
 * Test on link implementation.
 */
class LinkTest extends ILIAS_UI_TestBase
{
    public function getLinkFactory(): I\Link\Factory
    {
        return new I\Link\Factory();
    }

    public function test_implements_factory_interface(): void
    {
        $f = $this->getLinkFactory();

        $this->assertInstanceOf("ILIAS\\UI\\Component\\Link\\Factory", $f);
        $this->assertInstanceOf(
            "ILIAS\\UI\\Component\\Link\\Standard",
            $f->standard("label", "http://www.ilias.de")
        );
    }

    public function test_get_label(): void
    {
        $f = $this->getLinkFactory();
        $c = $f->standard("label", "http://www.ilias.de");

        $this->assertEquals("label", $c->getLabel());
    }

    public function test_get_action(): void
    {
        $f = $this->getLinkFactory();
        $c = $f->standard("label", "http://www.ilias.de");

        $this->assertEquals("http://www.ilias.de", $c->getAction());
    }

    public function test_render_link(): void
    {
        $f = $this->getLinkFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->standard("label", "http://www.ilias.de");

        $html = $r->render($c);

        $expected_html =
            '<a href="http://www.ilias.de">label</a>';

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function test_render_with_new_viewport(): void
    {
        $f = $this->getLinkFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->standard("label", "http://www.ilias.de")->withOpenInNewViewport(true);

        $html = $r->render($c);

        $expected_html =
            '<a href="http://www.ilias.de" target="_blank" rel="noopener">label</a>';

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function testRenderWithLanguage(): void
    {
        $language = $this->getMockBuilder(LanguageTag::class)->getMock();
        $language->method('__toString')->willReturn('en');
        $reference = $this->getMockBuilder(LanguageTag::class)->getMock();
        $reference->method('__toString')->willReturn('fr');

        $f = $this->getLinkFactory();
        $r = $this->getDefaultRenderer();

        $c = $f->standard("label", "http://www.ilias.de")
            ->withContentLanguage($language)
            ->withLanguageOfReferencedContent($reference);

        $html = $r->render($c);

        $expected_html =
            '<a lang="en" hreflang="fr" href="http://www.ilias.de">label</a>';

        $this->assertHTMLEquals($expected_html, $html);
    }

    public function testRenderWithHelpTopic(): void
    {
        $f = $this->getLinkFactory();
        $r = $this->getDefaultRenderer();
        $c = $f->standard("label", "http://www.ilias.de")
            ->withHelpTopics(new \ILIAS\UI\Help\Topic("a"));

        $html = $r->render($c);

        $expected_html = ''
            . '<div class="c-tooltip__container">'
            . '<a href="http://www.ilias.de" id="id_2" aria-describedby="id_1">label</a>'
            . '<div id="id_1" role="tooltip" class="c-tooltip--hidden"><p>tooltip: a</p></div>'
            . '</div>';

        $this->assertHTMLEquals($expected_html, $html);
    }
}
