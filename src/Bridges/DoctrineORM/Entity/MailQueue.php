<?php

namespace Simplement\Bridges\DoctrineORM\Entity;

use Nette\Mail\Message;
use Doctrine\ORM\Mapping as ORM;
use Simplement\MailQueue\IEntry;

/**
 * Description of MailQueue
 *
 * @author Martin Dendis <martin.dendis@improvisio.cz>
 *
 * @ORM\Entity
 */
class MailQueue implements IEntry {

	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="integer", options={"unsigned"=true})
	 * @ORM\GeneratedValue
	 */
	private $id;

	/**
	 *
	 * @var Message
	 */
	private $message;

	/**
	 * @ORM\Column(name="binary", type="blob")
	 */
	private $binary;

	/**
	 * @ORM\Column(name="priority", type="integer", options={"default"=1, "unsigned"=true})
	 */
	private $priority;

	/**
	 * @ORM\Column(name="attempt", type="integer", options={"default"=0, "unsigned"=true})
	 */
	private $attempt;

	/**
	 * @ORM\Column(name="sheduled_at", type="datetime", nullable=true)
	 */
	private $sheduledAt;

	public function __construct(Message $message, $priority = 1) {
		$this->binary = serialize($this->message = $message);
		$this->priority = $priority;
		$this->attempt = 0;
	}

	/**
	 *
	 * @return Message
	 */
	public function getMessage() {
		if ($this->message) {
			return $this->message;
		}
		if (is_resource($this->binary)) {
			rewind($this->binary);
			$data = stream_get_contents($this->binary);
		} else {
			$data = $this->binary;
		}
		return $this->message = unserialize($data);
	}

	/**
	 *
	 * @return int
	 */
	public function getPriority() {
		return $this->priority;
	}

	/**
	 *
	 * @return int
	 */
	public function getAttempt() {
		return $this->attempt;
	}

	/**
	 *
	 * @param int $attempt
	 * @return self
	 */
	public function setAttempt($attempt) {
		$this->attempt = $attempt;
		return $this;
	}

	/**
	 *
	 * @param \DateTime $sheduledAt
	 * @return self
	 */
	public function setSheduledAt(\DateTime $sheduledAt = NULL) {
		$this->sheduledAt = $sheduledAt;
		return $this;
	}

	public function __clone() {
		$this->id = NULL;
	}

}
