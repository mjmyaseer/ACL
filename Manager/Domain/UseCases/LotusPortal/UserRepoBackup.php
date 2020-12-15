<?php

namespace Manager\Domain\UseCases\LotusPortal;

Use Manager\Data\Repositories\Contracts\UserRepoInterface;

class UserRepo
{
    /**
     * @var UserRepoInterface
     */
    private $user;

    public function __construct(
        UserRepoInterface $user
    )
    {
        $this->user = $user;
    }

     public function getAllUsersCount(){
        return $this->user->getAllUsersCount();
    }
    
    public function getTierByBoard(){
        $result1 = $this->user->getTierOneBoardStatus();
        $result2 = $this->user->getTierTwoBoardStatus();
        $result3 = $this->user->getTierThreeBoardStatus();

        return $res = [
            'tier_one_rotation' => (int)env('BOARD_ALLOCATION') - $result1->tier_one_rotation,
            'tier_two_rotation' => (int)env('BOARD_ALLOCATION') - $result2->tier_two_rotation,
            'tier_three_rotation' => (int)env('BOARD_ALLOCATION') - $result3->tier_three_rotation
        ];
    }
    
    public function getTierOneBoard($id){
        return $this->user->getTierOneBoard($id);
    }

    public function getTierTwoBoard($id){
        return $this->user->getTierTwoBoard($id);
    }
    
    public function getTierThreeBoard($id){
        return $this->user->getTierThreeBoard($id);
    }
    public function getUserByID($id) {
        return $this->user->getUserByID($id);
    }

    public function getActiveUserID() {
        return $this->user->getActiveUserID();
    }

    public function getTierTwoActiveUserID(){
        return $this->user->getTierTwoActiveUserID();
    }

    public function getTierThreeActiveUserID(){
        return $this->user->getTierThreeActiveUserID();
    }

    public function getTierOneActiveUserID(){
        return $this->user->getTierOneActiveUserID();
    }
    public function getUserTierOne($id){
        return $this->user->getUserTierOne($id);
    }
    
    public function getUserTierTwo($id){
        return $this->user->getUserTierTwo($id);
    }
    
    public function getUserTierThree($id){
        return $this->user->getUserTierThree($id);
    }

    public function getTierOneBoardWithQueue($id){
        return $this->user->getTierOneBoardWithQueue($id);
    }
    
    public function getTierTwoBoardWithQueue($id){
        return $this->user->getTierTwoBoardWithQueue($id);
    }
    
    public function getTierThreeBoardWithQueue($id){
        return $this->user->getTierThreeBoardWithQueue($id);
    }
    public function updateUser($data) {

        
        $reason = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'promoCode' => $data['promo_code']
        ];

        $id =  ['id' => $data['id']];
        
        $updateOrCreate = $this->user->updateUser($id, $reason );
        // return $this->user->updateUser($data);
    }
    
    public function getDonation($id){

        $tier1 = $this->user->getUserByTier('tier_one',$id);
        $tier2 = $this->user->getUserByTier('tier_two',$id);
        $tier3 = $this->user->getUserByTier('tier_three',$id);

        $total = (count($tier1)*env('TIER_ONE_VAL') + count($tier2)*env('TIER_TWO_VAL') +  count($tier3)*env('TIER_THREE_VAL'));
        // dd($total);
        $res = [
            'tier1' =>$tier1,
            'tier2' =>$tier2,
            'tier3' =>$tier3,
            'total' =>$total
        ];

       return $res;
    }
    
    public function getNextDonation($table){

        switch ($table) {
        case "tier_one":
            $active_board = $this->user->getTierOneActiveUserID();
            break;
          case "tier_two":
            $active_board = $this->user->getTierTwoActiveUserID();
            break;
          case "tier_three":
            $active_board = $this->user->getTierThreeActiveUserID();
            break;
          default:
          $active_board = $this->user->getTierOneActiveUserID();
        }
        
        $board_id = $active_board->board_id;
        $name = $active_board->first_name . " " . $active_board->last_name;
        $id = $active_board->id;
        $next = $id + 1;
        $resultSet2=[];
        $resultSet2 = $this->user->getDynamicTableByID($table,'recipient',"'".$active_board->board_id."'");
        $num = (empty($resultSet2)) ? 0 : count($resultSet2);
        
        if($num == 0){
            for($i=0; $i<8; $i++)
            {
                $allocation = $i + 1;
                $next = $id + 1;
                $payee = $this->getPayPalPayee($allocation, $name, $next,$table);
                $resultSet2[$i] = new \stdClass();
                $resultSet2[$i]->creationDateTime = "Pending";
                $resultSet2[$i]->board_id = $board_id;
                $resultSet2[$i]->name = $name;
                $resultSet2[$i]->allocation = $i + 1;
                $resultSet2[$i]->payee = $payee;
                $resultSet2[$i]->status = "Pending";
                
            }
        }else{
            foreach($resultSet2 as $key => $field){
                
                $num = $key;
                $allocation = (empty($field->allocation)) ? 1 : $field->allocation;
                $payee = $this->getPayPalPayee($allocation, $name, $next,$table);
                $resultSet2[$key]->status = "Complete";
                $resultSet2[$key]->allocation = $allocation;
                $resultSet2[$key]->name = $name;
                $resultSet2[$key]->payee = $payee;
                $resultSet2[$key]->board_id = $board_id;
            }

            for($i=$num; $i<8; $i++) {
                $allocation = $i + 1;
                $payee = $this->getPayPalPayee($allocation, $name, $next,$table);
                $resultSet2[$i] = new \stdClass();
                $resultSet2[$i]->allocation = $allocation;
                $resultSet2[$i]->payee = $payee;
                $resultSet2[$i]->creationDateTime = "Pending";
                $resultSet2[$i]->status = "Pending";
                $resultSet2[$i]->name = $name;
                $resultSet2[$i]->board_id = $board_id;

            }
        }

        return $resultSet2;
    }
    
    
    public function getZeroDonation($table){
        $resultSet3 = $this->user->getTierOneZero();
        $allocation = 1;
        $res = 0;
        $finResult = [];

        foreach($resultSet3 as $key => $field){
            $board_id = $field->board_id;
            $name = $field->first_name. " ".$field->last_name;
            $id = $field->id;
            $next = $id + 1;

            $secondResult = [];
            for($i=0; $i<8; $i++) {
                
                $nKey = $key + 1;
                $allocation = $i + 1;
                $payee = $this->getPayPalPayee($allocation, $name, $next, $table);
                $resultSetN[0] = new \stdClass();
                $resultSetN[0]->creationDateTime = "Pending";
                $resultSetN[0]->payee = $payee;
                $resultSetN[0]->status = "Pending";
                $resultSetN[0]->allocation = $allocation;
                $resultSetN[0]->name = $name;
                $resultSetN[0]->board_id = $board_id;

                $secondResult = array_merge($secondResult,$resultSetN);
                }
                
                $finResult = array_merge($finResult,$secondResult);
        }

        return $finResult;
    }

    function getPayPalPayee($allocation, $name, $next, $table) {
        
        switch ($table) {
            case "tier_one":
                $getNextActive = $this->user->getDynamicTableByID('tier_one','id',$next);
                break;
              case "tier_two":
                $getNextActive = $this->user->getDynamicTableByID('tier_two','id',$next);
                break;
              case "tier_three":
                $getNextActive = $this->user->getDynamicTableByID('tier_three','id',$next);
                break;
              default:
              $getNextActive = $this->user->getDynamicTableByID('tier_one','id',$next);
            }

            $getTierUp = $this->user->getTierTwoActiveUserID();
            $payee = "";
        if($allocation == 1) {
            $payee = "Community";
        } elseif($allocation == 2 || $allocation == 3 || $allocation ==4) {
            $payee = $name;
        } elseif($allocation == 5 || $allocation == 6 || $allocation == 7) {
            $tier_up = $getTierUp;
            $payee = $tier_up->first_name ." ". $tier_up->last_name;
        } elseif($allocation == 8) {
            $payee = $getNextActive[0]->first_name. " ".$getNextActive[0]->last_name;
        }
        return $payee;
    }

    public function completedBoard($id){
        $res = $this->user->getCompletedBoardByID('tier_one',$id);

        return $res;
    }

}
