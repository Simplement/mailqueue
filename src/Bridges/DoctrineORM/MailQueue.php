<?php

namespace Simplement\Bridges\DoctrineORM;

use Nette\Mail\Message;
use Doctrine\ORM\EntityManager;
use Simplement\MailQueue\IEntry;
use Simplement\MailQueue\ITransactionQuery;
use Simplement\Bridges\DoctrineORM\Entity\MailQueue as Entry;

/**
 * Description of MailQueue
 *
 * @author Martin Dendis <martin.dendis@improvisio.cz>
 */
class MailQueue implements ITransactionQuery {

	/** @var EntityManager */
	private $em;

	/** @var array */
	private $queue = array();

	/** @var bool */
	private $inTransaction = FALSE;

	public function __construct(EntityManager $em) {
		$this->em = $em;
	}

	public function add(Message $message, $priority = 1) {
		$this->em->persist(new Entry($message, $priority));

		if (!$this->em->getConnection()->isTransactionActive()) {
			$this->em->flush();
		}
	}

	public function addRescheduled(IEntry $e, \DateTime $at = NULL) {
		if (!$e instanceof Entry) {
			throw new \InvalidArgumentException('Entry must be instance of Simplement\MailQueue\IEntry');
		}

		$e = clone $e;
		$e->setSheduledAt($at);
		$this->em->persist($e);

		if (!$this->em->getConnection()->isTransactionActive()) {
			$this->em->flush();
		}
	}

	public function beginTransaction() {
		$this->em->beginTransaction();
		$this->inTransaction = TRUE;
	}

	public function commit() {
		$this->em->flush();
		$this->em->commit();
		$this->inTransaction = FALSE;
	}

	public function pop() {
		if (!$this->queue) {
			$this->fetchQueue();
		}
		if ($this->queue) {
			$e = array_shift($this->queue);
			$this->em->remove($e);

			if (!$this->em->getConnection()->isTransactionActive()) {
				$this->em->flush();
			}

			return $e;
		}
		return NULL;
	}

	private function fetchQueue() {
		$this->em->flush();

		$query = $this->em->createQueryBuilder()
			->addSelect('e')
			->from('Simplement\Bridges\DoctrineORM\Entity\MailQueue', 'e')
			->andWhere('e.sheduledAt IS NULL OR e.sheduledAt <= :now')
			->setParameter('now', new \DateTime)
			->addOrderBy('e.priority', 'ASC')
			->setFirstResult(0)
			->setMaxResults(50)
			->getQuery();

		if ($this->inTransaction) {
			$query->setLockMode(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE);
		}

		$this->queue = $query->getResult();
	}

	public function size() {
		$this->em->flush();

		return $this->em->createQueryBuilder()
				->addSelect('COUNT(e)')
				->from('Simplement\Bridges\DoctrineORM\Entity\MailQueue', 'e')
				->getQuery()
				->getSingleScalarResult();
	}

}
