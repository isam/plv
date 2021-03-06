<?php

namespace Plv\Versions;

use Symfony\Component\DomCrawler\Crawler;

class ScalaVersionsTest extends \PHPUnit_Framework_TestCase
{
    protected static $html;

    public static function setupBeforeClass()
    {
        static::$html = file_get_contents(__DIR__.'/../../Fixtures/scala.html');
    }

    public function testGetName()
    {
        $pv = new ScalaVersions();
        $this->assertSame('Scala', $pv->getName());
    }

    public function testGetUrl()
    {
        $pv = new ScalaVersions();
        $this->assertSame('http://www.scala-lang.org/downloads', $pv->getUrl());
    }

    public function testGetFilterValue()
    {
        $crawler = new Crawler();
        $pv = new ScalaVersions();

        $crawler->addHtmlContent(static::$html);
        $filters = $pv->getFilterValue();

        $items = array();
        foreach ($filters as $filter) {
            $items = array_merge($items, $crawler->filter($filter)->each(function (Crawler $crawler, $i) {
                return $crawler->text();
            }));
        }

        $this->assertSame(array('div.main-page-column > ul > li'), $pv->getFilterValue());
        $this->assertGreaterThanOrEqual(3, count($items));

        return $items;
    }

    /**
     * @depends	testGetFilterValue
     */
    public function testGetCallback($items)
    {
        $pv = new ScalaVersions();
        $callback = $pv->getCallback();
        $version_str = $callback($items);

        $this->assertTrue(is_callable($callback));
        $this->assertGreaterThanOrEqual(3, count($version_str));
        foreach ($version_str as $str) {
            $this->assertRegExp('/^[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}/', $str);
        }
    }

    public function testGetInstalledVersion()
    {
        $pv = new ScalaVersions();
        $version = null;

        $version_str = exec('/usr/bin/env scala -version 2>&1');
        if (preg_match('/^.*([0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}).*$/', $version_str, $m)) {
            $version = $m[1];
        }

        $this->assertSame($version, $pv->getInstalledVersion());
    }
}
