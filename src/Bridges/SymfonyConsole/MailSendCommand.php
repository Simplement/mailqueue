<?php

namespace Simplement\Bridges\SymfonyConsole;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Description of MailSendCommand
 *
 * @author Martin Dendis <martin.dendis@improvisio.cz>
 */
class MailSendCommand extends Command {

	/** @var \Nette\Mail\IMailer @inject */
	public $mailer;

	/** @var string */
	private $config;

	public function setConfig(array $config) {
		$this->config = $config;
	}

	protected function configure() {
		$this->setName('mailqueue:send')
			->setDescription('Send mail to given email address. Useful to determine whether mail queue is working properly')
			->addArgument('recipient', InputArgument::REQUIRED, 'Recipient email')
			->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'Sender email', $this->config['defaultSender'])
			->addOption('subject', 's', InputOption::VALUE_REQUIRED, 'Email subject', '')
			->addOption('message', 'm', InputOption::VALUE_REQUIRED, 'Email message', '')
			->addOption('no-queue', 'n', InputOption::VALUE_NONE, 'Skip mail queue & send mail directly.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$message = new \Nette\Mail\Message;

		if (!$this->validateEmail($from = trim($input->getOption('from')))) {
			$output->writeln('<error>Expected valid email address, given "' . $from . '"</error>');
			return 1;
		}
		$message->setFrom($from);

		if (!$this->validateEmail($to = trim($input->getArgument('recipient')))) {
			$output->writeln('<error>Expected valid email address, given "' . $to . '"</error>');
			return 1;
		}
		$message->addTo($to);

		$message->setSubject($input->getOption('subject'));

		$message->setBody($input->getOption('message'));

		if ($input->getOption('force') && $this->mailer instanceof \Simplement\MailQueue\Mailer) {
			$this->mailer->send($message, 1, FALSE);
		} else {
			$this->mailer->send($message);
		}
	}

	private function validateEmail($email) {
		if (preg_match('#^(.+) +<(.*)>\z#', $email, $matches)) {
			$email = $matches[2];
		} else {
			$email = $email;
		}
		return \Nette\Utils\Validators::isEmail($email);
	}

}
