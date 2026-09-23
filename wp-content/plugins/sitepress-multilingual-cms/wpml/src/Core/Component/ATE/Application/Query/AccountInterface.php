<?php

namespace WPML\Core\Component\ATE\Application\Query;

use WPML\Core\Component\ATE\Application\Query\Dto\AccountBalanceDto;
use WPML\Core\Component\ATE\Application\Query\Dto\CreditInfoDto;

interface AccountInterface {


  public function getCredits( bool $allowCached = false ): CreditInfoDto;


  public function getAccountBalances( bool $allowCached = false ): AccountBalanceDto;


}
