<?php

namespace Simplement\MailQueue;

use Nette\Mail\Message;

/**
 * Description of Entry
 *
 * @author Martin Dendis <martin.dendis@improvisio.cz>
 */
interface IEntry {

	/** @return Message */
	public function getMessage();

	/** @return int */
	public function getPriority();

	/** @return int */
	public function getAttempt();

	/** @param int $attempt */
	public function setAttempt($attempt);

}
