<?php

namespace Simplement\MailQueue;

use Nette\Mail\Message;

/**
 * Description of MailQueue
 *
 * @author Martin Dendis <martin.dendis@improvisio.cz>
 */
interface IQueue {

	/**
	 * Add mail into queue
	 *
	 * @param Message $message
	 * @param int $priority
	 */
	public function add(Message $message, $priority = 1);

	/**
	 * Reschedule mail in queue
	 *
	 * @param IEntry $e
	 * @param \DateTime $at
	 */
	public function addRescheduled(IEntry $e, \DateTime $at = NULL);

	/**
	 * Remove & return message from top of the queue
	 *
	 * @return IEntry|NULL $e
	 */
	public function pop();

	/**
	 * Return's count of messages in queue
	 *
	 * @return int
	 */
	public function size();

}
