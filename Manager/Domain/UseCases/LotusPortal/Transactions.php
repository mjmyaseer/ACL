<?php

namespace Manager\Domain\UseCases\LotusPortal;

Use Manager\Data\Repositories\Contracts\TransactionRepoInterface;
Use Manager\Data\Repositories\Contracts\UserRepoInterface;
use Manager\Data\Models\Transaction;
use Manager\Data\Models\TierOne;

class Transactions
{
    /**
     * @var TransactionRepoInterface
     */
    private $transactions;
    private $user;

    public function __construct(
        TransactionRepoInterface $transactions,
        UserRepoInterface $user
    )
    {
        $this->transactions = $transactions;
        $this->user = $user;
    }

     public function getRedeemTotal($data){
        return $this->transactions->getRedeemTotal($data);
    }

    public function redeemEligibility($data){
        $recipientsList = array();

        $subscriptions = $this->user->getDynamicTableByID(
            Transaction::TABLE,'userid',"'".$data['id']."' and redeemed = 0"
        );

        if($subscriptions != null){
            $subscriptionFirst = $subscriptions[0]->created_at;
            $userBoards = $this->user->getDynamicTableByID(
                TierOne::TABLE,'uniqueID',"'".$data['uniqueID']."'"
            );

            if($userBoards != null){
                foreach ($userBoards as $board){
                    $boardsList[] = $board->board_id;
                    $recipients = $this->user->getDynamicTableByID(
                        TierOne::TABLE,'recipient',"'".$board->board_id."' and creationDateTime >".
                        "'".$subscriptionFirst."'"
                    );
                    if($recipients != null){
                        $recipientsList[] = $recipients;
                    }
                }
            }
        }

        if($recipientsList != null)
        {
            return True;
        }else{
            return False;
        }
    }

    public function makeRedeem($data){
        $stripe_data = $this->transactions->makeRedeem($data);
        return $stripe_data;
    }

    public function approveRedeem(){
        return $this->transactions->approveRedeem();
    }

    public function markRedeem($id){
        return $this->transactions->markRedeem($id);
    }
}