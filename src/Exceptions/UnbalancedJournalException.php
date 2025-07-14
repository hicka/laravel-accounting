<?php
namespace Hickr\Accounting\Exceptions;

use Exception;

class UnbalancedJournalException extends Exception
{
protected $debit;
protected $credit;

public function __construct($debit, $credit)
{
parent::__construct("Unbalanced journal: Debits [$debit] ≠ Credits [$credit]");
$this->debit = $debit;
$this->credit = $credit;
}

public function getDebit()
{
return $this->debit;
}

public function getCredit()
{
return $this->credit;
}
}