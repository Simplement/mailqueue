<?php

/**
 * Test: Simplement\MailQueue\Extension.
 *
 * @testCase SimplementTests\MailQueue\ExtensionTest
 * @author Martin Dendis <martin.dendis@email.cz>
 * @package Simplement\MailQueue
 */

namespace SimplementTests\MailQueue;

use Simplement;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Martin Dendis <martin.dendis@email.cz>
 */
class ExtensionTest extends Tester\TestCase {

	private function prepareConfigurator() {
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);
		$config->addParameters(array('container' => array('class' => 'SystemContainer_' . Nette\Utils\Strings::random())));
		$config->addConfig(__DIR__ . '/config/common.neon', $config::NONE);
		$config->addConfig(__DIR__ . '/config/default.neon', $config::NONE);

		return $config;
	}

	public function testFunctionality() {
		$config = $this->prepareConfigurator();
		$container = $config->createContainer();

		$mailer = $container->getByType('Nette\Mail\IMailer');
		/** @var implement\MailQueue\Mailer $mailer */
		Assert::true($mailer instanceof Simplement\MailQueue\Mailer);

		$app = $container->getService('console.application');
		/** @var \Kdyby\Console\Application $app */
		Assert::true($app instanceof \Kdyby\Console\Application);
		Assert::equal(3, count($app->all('mailqueue')));
	}

//	public function testShortUrl() {
//		$this->invokeTestOnConfig(__DIR__ . '/config/short-url.neon');
//	}
//
//	public function testUrlWithoutTld() {
//		$this->invokeTestOnConfig(__DIR__ . '/config/url-without-tld.neon');
//	}
//
//	private function invokeTestOnConfig($file) {
//		$config = $this->prepareConfigurator();
//		$config->addConfig($file, $config::NONE);
//		Assert::true($config->createContainer() instanceof Nette\DI\Container);
//	}

}

\run(new ExtensionTest());
