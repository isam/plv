<?php

namespace Plv\Versions;

use Plv\Versions\RubyVersions;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class RubyVersionsTest extends \PHPUnit_Framework_TestCase
{
	private $html;
	private $url = 'http://www.ruby-lang.org/ja/downloads/';

	protected function setup()
	{
		$client = new Client();
		$client->request('GET', $this->url);
		$this->html = $client->getResponse()->getContent();
	}

	public function testGetName()
	{
		$rv = new RubyVersions();
		$this->assertSame('Ruby', $rv->getName());
	}

	public function testGetUrl()
	{
		$rv = new RubyVersions();
		$this->assertSame($this->url, $rv->getUrl());
	}

	public function testGetFilterValue()
	{
		$crawler = new Crawler();
		$rv = new RubyVersions();

		$crawler->addHtmlContent($this->html);
		$items = $crawler->filter($rv->getFilterValue());

		$this->assertSame('div#content > ul > li', $rv->getFilterValue());
		$this->assertGreaterThanOrEqual(3, count($items));
	}

	public function testGetCallback()
	{
		$crawler = new Crawler();
		$rv = new RubyVersions();

		$callback = $rv->getCallback();
		$crawler->addHtmlContent($this->html);

		$items = $crawler->filter($rv->getFilterValue())->each(function ($crawler, $i) {
			return $crawler->text();
		});

		$version_str = $callback($items);

		$this->assertTrue(is_callable($callback));
		$this->assertGreaterThanOrEqual(3, count($version_str));
		foreach ($version_str as $str) {
			$this->assertRegExp('/^[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}/', $str);
		}
	}

	public function testGetInstalledVersion()
	{
		$rv = new RubyVersions();
		$version = null;

		$version_str = exec('/usr/bin/env ruby --version 2>&1');
		if (preg_match('/^ruby ([0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}(p[0-9]{1,})?).*$/', $version_str, $m)) {
			$version = $m[1];
		}

		$this->assertSame($version, $rv->getInstalledVersion());
	}
}