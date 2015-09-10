<?php

namespace Simplement\Bridges\MailDI;

use Nette\DI\CompilerExtension;

/**
 * Description of MailExtension
 *
 * @author Martin Dendis <martin.dendis@improvisio.cz>
 */
class MailExtension extends CompilerExtension {

	/** @var array */
	private $defaults = array(
		'timeLimit' => 30,
		'mailLimit' => 50,
		'attemptLimit' => 5,
		'rescheduleTime' => '+30 minutes',
	);

	public function loadConfiguration() {
		$containerBuilder = $this->getContainerBuilder();

		$this->validateConfig($this->defaults);            

		$containerBuilder->addDefinition($this->prefix('command.status'))
			->setClass('Simplement\Bridges\SymfonyConsole\MailStatusCommand')
			->addTag('kdyby.console.command');

		$containerBuilder->addDefinition($this->prefix('command.sentfronted'))
			->setClass('Simplement\Bridges\SymfonyConsole\MailSendFrontedCommand')
			->addSetup('setConfig', array($this->config))
			->addTag('kdyby.console.command');   
	}

	public function beforeCompile() {
		$containerBuilder = $this->getContainerBuilder();

		$mailerAlias = $containerBuilder->getByType(\Nette\Mail\IMailer::class);

		$mailerDefinition = $containerBuilder
			->getDefinition($mailerAlias);

		$mailerFactory = $mailerDefinition->getFactory();

		$mailerDefinition->setFactory('Simplement\MailQueue\Mailer', array($mailerFactory));
	}

}
