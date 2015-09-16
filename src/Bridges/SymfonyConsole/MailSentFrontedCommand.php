<?php

namespace Simplement\Bridges\SymfonyConsole;

use Nette\Mail\IMailer;
use Simplement\MailQueue\IQueue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of MailSendFrontedCommand
 *
 * @author Martin Dendis <martin.dendis@improvisio.cz>
 */
class MailSendFrontedCommand extends Command {

	/** @var IMailer @inject */
	public $mailer;

	/** @var IQueue @inject */
	public $queue;

	/** @var array */
	private $config;

	public function setConfig(array $config) {
		$this->config = $config;
	}

	protected function configure() {
		$this->setName('mailqueue:sendfronted')
			->setDescription('Send dose of emails in queue. (limited by setting of extension)');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$timeLimit = microtime(TRUE);

		if (set_time_limit($this->config['timeLimit']) || ($maxExecutionTime = ini_get('max_execution_time'))) {
			$timeLimit += $this->config['timeLimit'];
		} else {
			$remainingTime = max(array($maxExecutionTime - 5, 1));
			$timeLimit += min(array($remainingTime, $this->config['timeLimit'])) - .5;
		}

		$mailLimit = $this->config['mailLimit'];
		$attemptLimit = $this->config['attemptLimit'];
		$rescheduleTime = new \DateTime($this->config['rescheduleTime']);

		if ($this->queue instanceof \Simplement\MailQueue\ITransactionQuery) {
			$this->queue->beginTransaction();
		}

		try {
			do {
				if (!($entry = $this->queue->pop())) {
					break;
				}

				$message = $entry->getMessage();

				try {
					$this->mailer->send($message, $entry->getPriority(), FALSE);
				} catch (\RuntimeException $e) {
					$this->queue->addRescheduled($entry, new \DateTime);
					throw $e;
				} catch (\Exception $e) {
					\Tracy\Debugger::log($e, \Tracy\Debugger::EXCEPTION);

					if (($attempt = $entry->getAttempt()) < $attemptLimit) {
						$entry->setAttempt($attempt + 1);
						$this->queue->addRescheduled($entry, $rescheduleTime);
					} else {
						$recipients = implode(', ', array_keys(
								array_merge(
									(array) $message->getHeader('To'), (array) $message->getHeader('Cc'), (array) $message->getHeader('Bcc')
						)));

						\Tracy\Debugger::log('Unnable to send mail to ' . $recipients, ', with subject ' . $message->subject);
					}
				}
			} while ($timeLimit > microtime(TRUE) && --$mailLimit);
		} catch (\Exception $e) {
			if ($this->queue instanceof \Simplement\MailQueue\ITransactionQuery) {
				$this->queue->commit();
			}
			throw $e;
		}

		if ($this->queue instanceof \Simplement\MailQueue\ITransactionQuery) {
			$this->queue->commit();
		}
	}

}
