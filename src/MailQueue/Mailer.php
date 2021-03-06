<?php

namespace Simplement\MailQueue;

use Nette\Mail\IMailer,
	Nette\Mail\Message;

/**
 * Description of Mailer
 *
 * @author Martin Dendis <martin.dendis@improvisio.cz>
 */
class Mailer implements IMailer {

	/** @var IMailer */
	private $mailer;

	/** @var IQueue */
	private $queue;

	/** @var array */
	private $config;

	public function __construct(IMailer $mailer, IQueue $queue) {
		$this->mailer = $mailer;
		$this->queue = $queue;
	}

	public function setConfig(array $config) {
		$this->config = $config;
	}

	/**
	 *
	 * @param Message $mail
	 * @param type $priority
	 * @param type $useQueue
	 */
	public function send(Message $mail, $priority = 1, $useQueue = TRUE) {
		if (!$mail->getFrom() && $this->config['defaultSender']) {
			$mail = clone $mail;
			$mail->setFrom($this->config['defaultSender']);
		}

		if (!$useQueue) {
			$this->mailer->send($mail);
		} else {
			$this->queue->add($mail, $priority);
		}
	}

	/**
	 * Return's direct mailer for mailing without using mail queue
	 *
	 * @return IMailer
	 */
	public function getDirectMailer() {
		return $this->mailer;
	}

}
