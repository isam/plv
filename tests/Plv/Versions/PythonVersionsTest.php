<?php

namespace Plv\Versions;

use Plv\Versions\PythonVersions;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class PythonVersionsTest extends \PHPUnit_Framework_TestCase
{
	private $html;
	private $url = 'http://www.python.org/download/';

	protected function setup()
	{
		$client = new Client();
		$client->request('GET', $this->url);
		$this->html = $client->getResponse()->getContent();
	}

	public function testGetName()
	{
		$pv = new PythonVersions();
		$this->assertSame('Python', $pv->getName());
	}

	public function testGetUrl()
	{
		$pv = new PythonVersions();
		$this->assertSame($this->url, $pv->getUrl());
	}

	public function testGetFilterValue()
	{
		$crawler = new Crawler();
		$pv = new PythonVersions();

		$crawler->addHtmlContent($this->html);
		$items = $crawler->filter($pv->getFilterValue());

		$this->assertSame('div#download-python > p > a', $pv->getFilterValue());
		$this->assertGreaterThanOrEqual(2, count($items));
	}

	public function testGetCallback()
	{
		$crawler = new Crawler();
		$pv = new PythonVersions();

		$callback = $pv->getCallback();
		$crawler->addHtmlContent($this->html);

		$items = $crawler->filter($pv->getFilterValue())->each(function ($crawler, $i) {
			return $crawler->text();
		});

		$version_str = $callback($items);

		$this->assertTrue(is_callable($callback));
		$this->assertGreaterThanOrEqual(2, count($version_str));
		foreach ($version_str as $str) {
			$this->assertRegExp('/^[0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}/', $str);
		}
	}

	public function testGetInstalledVersion()
	{
		$pv = new PythonVersions();
		$version = null;

		$version_str = exec('/usr/bin/env python -V 2>&1');
		if (preg_match('/^.*([0-9]{1,}\.[0-9]{1,}\.[0-9]{1,}).*$/', $version_str, $m)) {
			$version = $m[1];
		}

		$this->assertSame($version, $pv->getInstalledVersion());
	}
}