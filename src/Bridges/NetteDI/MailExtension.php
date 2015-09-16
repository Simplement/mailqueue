<?php

namespace Simplement\Bridges\MailDI;

use Nette\DI\CompilerExtension;

/**
 * Description of MailQueueExtension
 *
 * @author Martin Dendis <martin.dendis@improvisio.cz>
 */
class MailQueueExtension extends CompilerExtension {

	/** @var array */
	private $defaults = array(
		'timeLimit' => 30,
		'mailLimit' => 50,
		'attemptLimit' => 5,
		'rescheduleTime' => '+30 minutes',
		'defaultSender' => NULL,
	);

	public function loadConfiguration() {
		$containerBuilder = $this->getContainerBuilder();

		$this->validateConfig($this->defaults);

		if (($defaultSender = $this->defaults['defaultSender']) &&
			!\Nette\Utils\Validators::isEmail($this->getMail($defaultSender))) {
			throw new \InvalidArgumentException('Expected default sender email, given "' . $defaultSender . '"');
		}

		$containerBuilder->addDefinition($this->prefix('command.status'))
			->setClass('Simplement\Bridges\SymfonyConsole\MailStatusCommand')
			->addTag('kdyby.console.command');

		$containerBuilder->addDefinition($this->prefix('command.sendfronted'))
			->setClass('Simplement\Bridges\SymfonyConsole\MailSendFrontedCommand')
			->addTag('kdyby.console.command');

		$containerBuilder->addDefinition($this->prefix('command.send'))
			->setClass('Simplement\Bridges\SymfonyConsole\MailSendCommand')
			->addTag('kdyby.console.command');
	}

	public function beforeCompile() {
		$containerBuilder = $this->getContainerBuilder();

		$mailerAlias = $containerBuilder->getByType('Nette\Mail\IMailer');

		$mailerDefinition = $containerBuilder
			->getDefinition($mailerAlias);

		$mailerFactory = $mailerDefinition->getFactory();

		$mailerDefinition->setFactory('Simplement\MailQueue\Mailer', array($mailerFactory))
			->addSetup('setConfig', array($this->config));

		if (!$this->config['defaultSender'] &&
			isset($mailerFactory->arguments['smtp']) &&
			$mailerFactory->arguments['smtp'] &&
			isset($mailerFactory->arguments['username']) &&
			($email = $mailerFactory->arguments['username']) &&
			\Nette\Utils\Validators::isEmail($this->getMail($email))) {

			$this->config['defaultSender'] = $email;
		}

		$containerBuilder->getDefinition($this->prefix('command.sendfronted'))
			->addSetup('setConfig', array($this->config));

		$containerBuilder->getDefinition($this->prefix('command.send'))
			->addSetup('setConfig', array($this->config));
	}

	private function getMail($email) {
		if (preg_match('#^(.+) +<(.*)>\z#', $email, $matches)) {
			return $matches[2];
		} else {
			return $email;
		}
	}

}
