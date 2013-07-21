<?php

namespace Plv\Versions;

use Plv\Versions\PerlVersions;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class PerlVersionsTest extends \PHPUnit_Framework_TestCase
{
	private $html;
	private $url = 'http://perl.org/';

	protected function setup()
	{
		$client = new Client();
		$client->request('GET', $this->url);
		$this->html = $client->getResponse()->getContent();
	}

	public function testGetName()
	{
		$pv = new PerlVersions();
		$this->assertSame('Perl', $pv->getName());
	}

	public function testGetUrl()
	{
		$pv = new PerlVersions();
		$this->assertSame($this->url, $pv->getUrl());
	}

	public function testGetFilterValue()
	{
		$crawler = new Crawler();
		$pv = new PerlVersions();

		$crawler->addHtmlContent($this->html);
		$items = $crawler->filter($pv->getFilterValue());

		$this->assertSame('div#short_lists > div.quick_links > div.list > p > a', $pv->getFilterValue());
		$this->assertGreaterThan(1, count($items));
	}

	public function testGetCallback()
	{
		$crawler = new Crawler();
		$pv = new PerlVersions();

		$callback = $pv->getCallback();
		$crawler->addHtmlContent($this->html);

		$items = $crawler->filter($pv->getFilterValue())->each(function ($crawler, $i) {
			return $crawler->text();
		});

		$version_str = $callback($items);

		$this->assertTrue(is_callable($callback));
		$this->assertEquals(1, count($version_str));
		$this->assertRegExp('/^[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}$/', $version_str[0]);
	}

	public function testGetInstalledVersion()
	{
		$pv = new PerlVersions();
		$version = null;

		exec('/usr/bin/env perl --version 2>&1', $output);
		foreach ($output as $line) {
			if (preg_match('/^.*([0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}).*$/', $line, $m)) {
				$version = $m[1];
				break;
			}
		}

		$this->assertSame($version, $pv->getInstalledVersion());
	}
}