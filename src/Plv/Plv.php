<?php

namespace Plv;

use Plv\Collection\VersionsCollection;
use Plv\Versions\VersionsInterface;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Finder\Finder;

class Plv
{
	private $versions;

	public function __construct(VersionsCollection $versions)
	{
		$this->versions = $versions;
	}

	public function execute()
	{
		$output = new ConsoleOutput();

		foreach ($this->versions as $versions) {
			$output->writeln(sprintf("<info>Check the version of %s</info>", $versions->getName()));

			$client = new Client();
			$items = $client->request('GET', $versions->getUrl())->filter($versions->getFilterValue())->each(function (Crawler $crawler, $i) {
				return $crawler->text();
			});

			$callback = $versions->getCallback();
			if (is_callable($callback)) {
				$items = $callback($items);
			}

			$installed_version = $versions->getInstalledVersion();
			if (is_null($installed_version)) {
				$installed_version = '<fg=red;>not installed</fg=red>';
			} else {
				$items = array_map(function ($item) use ($installed_version) {
					if (version_compare($item, $installed_version, '>')) {
						$item = '<fg=red;>New</fg=red>' . $item;
					}
					return $item;
				}, $items);
			}

			$output->writeln(sprintf('current version : <comment>%s</comment>', implode(' ', $items)));
			$output->writeln(sprintf('installed version : <comment>%s</comment>', $installed_version));
			$output->writeln('');
		}
	}

	public static function findByLanguageFile()
	{
		$finder = new Finder();
		return $finder
			->files()
			->name('*Versions.php')
			->in(ROOT_PATH . 'src/Plv/Versions')
			->sortByName();
	}

	public static function toLanguageName(\SplFileInfo $file)
	{
		return strtolower(substr(basename($file->getRealpath()), 0, -12));
	}
}
