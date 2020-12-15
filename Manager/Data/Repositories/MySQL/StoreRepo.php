<?php


namespace Manager\Data\Repositories\MySQL;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Manager\Data\Models\User;
use Manager\Data\Models\Transaction;
use Manager\Data\Models\LegacyBucksRedemption;
use Manager\Data\Models\TierOne;
use Manager\Data\Models\TierTwo;
use Manager\Data\Models\TierThree;
use Manager\Data\Models\Customer;
use Manager\Data\Repositories\Contracts\StoreInterface;

class StoreRepo implements StoreInterface
{

    public function index(){

            return 1;
    }

    
}