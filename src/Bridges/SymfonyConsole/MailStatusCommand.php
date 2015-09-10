<?php

namespace Simplement\Bridges\SymfonyConsole;

use Simplement\MailQueue\IQueue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of MailStatusCommand
 *
 * @author Martin Dendis <martin.dendis@improvisio.cz>
 */
class MailStatusCommand extends Command {

	/** @var IQueue @inject */
	public $mailQueue;

	protected function configure() {
		$this->setName('mailqueue:status')
			->setDescription('Return\'s count of mails to be sent.');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$output->writeln($this->mailQueue->size());
	}

}
