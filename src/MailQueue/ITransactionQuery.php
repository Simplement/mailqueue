<?php

namespace Simplement\MailQueue;

/**
 *
 * @author Martin Dendis <martin.dendis@improvisio.cz>
 */
interface ITransactionQuery extends IQueue {

	public function beginTransaction();

	public function commit();

}
