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
        $payee2 = "";
        
        if($num == 0 ){
            for($i=0; $i<8; $i++)
            {
                $allocation = $i + 1;
                $next = $id + 1;
                $payee = $this->newPayee($allocation, $name);
                $resultSet2[$i] = new \stdClass();
                $resultSet2[$i]->creationDateTime = "Pending";
                $resultSet2[$i]->board_id = $board_id;
                $resultSet2[$i]->name = $name;
                $resultSet2[$i]->allocation = $i + 1;
                $resultSet2[$i]->payee1 = $payee['payee1'];
                $resultSet2[$i]->payee2 = $payee['payee2'];
                $resultSet2[$i]->status = "Pending";
                
            }
        }else{
            
            foreach($resultSet2 as $key => $field){
                
                $num = $key +1;
                $allocation = (empty($field->allocation)) ? 1 : $field->allocation;
                $payee = $this->getPayPalPayee($allocation, $name, $next,$table);

                $payees = $this->tierAllocation($allocation);
               
                $resultSet2[$key]->status = "Complete";
                $resultSet2[$key]->allocation = $allocation;
                $resultSet2[$key]->name = $name;
                $resultSet2[$key]->payee = $payee;
                $resultSet2[$key]->payee1 = $payees['payee1'];
                $resultSet2[$key]->payee2 = $payees['payee2'];
                $resultSet2[$key]->board_id = $board_id;
            }
                
            for($i=$num; $i<8; $i++) {
                $allocation = $i + 1;
                $payees = $this->tierAllocation($allocation);
                $resultSet2[$i] = new \stdClass();
                $resultSet2[$i]->allocation = $allocation;
                $resultSet2[$i]->payee1 = $payees['payee1'];
                $resultSet2[$i]->payee2 = $payees['payee2'];
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
            $payee1 = '';
            $payee2 = '';

            if(isset($field->payee1)){
                $payee1UserByEMail = $this->user->getDynamicTableByID('users','paypal_email',"'".
                $field->payee1."'");

                if(!isset($payee1UserByEMail[0])){
                    $payee1UserByEMail = $this->user->getDynamicTableByID('users','email',"'".
                    $field->payee1."'");
                }
            }
            
            if(isset($field->payee2)){
                $payee2UserByEMail = $this->user->getDynamicTableByID('users','paypal_email',"'".
                $field->payee2."'");

                if(!isset($payee2UserByEMail[0])){
                    $payee1UserByEMail = $this->user->getDynamicTableByID('users','email',"'".
                    $field->payee2."'");
                }
            }
            
            if(isset($payee1UserByEMail[0])){
                $payee1 = $payee1UserByEMail[0]->name;
            }elseif(isset($field->payee1) && ($field->payee1 == "Community")){
                $payee1 = "Community";
            }elseif(isset($field->payee1) && ($field->payee1 == "Donation")){
                $payee1 = "Donation";
            }

            if(isset($payee2UserByEMail[0])){
                $payee2 = $payee2UserByEMail[0]->name;
            }elseif(isset($field->payee2) && ($field->payee2 == "Community")){
                $payee2 = "Community";
            }elseif(isset($field->payee2) && ($field->payee2 == "Donation")){
                $payee2 = "Donation";
            }

            $secondResult = [];
            for($i=0; $i<8; $i++) {
                
                $nKey = $key + 1;
                $allocation = $i + 1;
                $payees = $this->tierAllocation($allocation);
                $resultSetN[0] = new \stdClass();
                $resultSetN[0]->creationDateTime = "Pending";
                $resultSetN[0]->payee1 = $payees['payee1'];
                $resultSetN[0]->payee2 = $payees['payee2'];
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
            $payee1 = "Community";
        } elseif($allocation == 2 || $allocation == 3 || $allocation ==4) {
            $payee = $name;
        } elseif($allocation == 5 || $allocation == 6 || $allocation == 7) {
            $tier_up = $getTierUp;
            $payee = $tier_up->first_name ." ". $tier_up->last_name;
        } elseif($allocation == 8) {
            if(!isset($getNextActive[0])){
            }
            $payee = $getNextActive[0]->first_name. " ".$getNextActive[0]->last_name;
        }
        return $payee;
    }

    function newPayee($allocation, $name) {
        $active_board2 = $this->user->getTierTwoActiveUserID();
        $uniqueID2 = $active_board2->uniqueID;
        $board_id2 = $active_board2->board_id;
        $tierUpBoard = $this->user->getDynamicTableByID('tier_one','t2_recipient',"'".$board_id2."'");
        $num2 = (empty($resultSet2)) ? 0 : count($resultSet2);

            $payee = [];
        if($allocation == 1 || $allocation == 6 || $allocation == 7) {
            $payee['payee1'] = "Community";
            $payee['payee2'] = "";
        } elseif($allocation == 8) {
            $payee['payee1'] = "Donation";
            $payee['payee2'] = "";
        } elseif($allocation == 2 || $allocation == 3 || $allocation == 4){
            $payee['payee1'] = $name;
            $payee['payee2'] = "";
        }elseif($allocation == 5){
            $payee['payee1'] = $name;
            if($num2 < 3) {
                $payee['payee2'] = "Community";
            } elseif($num2 < 17) {
                $det = $this->user->getDynamicTableByID('users','uniqueID',"'".
                $uniqueID2."'");
                $payee['payee2'] = $det[0]->name;
            }elseif($num == 17) {
                $payee['payee2'] = getTierUp3PayPalEmail();
            }
            else{
                $payee['payee2'] = "Donation";
            }
        }
        return $payee;
    }

    public function completedBoard($id){
        $res = $this->user->getCompletedBoardByID('tier_one',$id);
        return $res;
    }

    public function tierAllocation($allocation){

        $payee = [];
        if($allocation == 1) {
            $payee['payee1'] = "Community";
            $payee['payee2'] = "";
        } 
        
        elseif($allocation == 2 || $allocation == 3 || $allocation ==4) {
            $activeBoardOne = $this->user->getTierOneActiveUserID();
            $uniqueID1 = $activeBoardOne->uniqueID;
            $result1 = $this->user->getDynamicTableByID(
                'users','uniqueID',"'".$uniqueID1."'"
            );
            $payee['payee1'] = $result1[0]->name;
            $payee['payee2'] = "";

        }elseif($allocation == 5){
            $activeBoardOne = $this->user->getTierOneActiveUserID();
            $uniqueID1 = $activeBoardOne->uniqueID;
            $result1 = $this->user->getDynamicTableByID(
                'users','uniqueID',"'".$uniqueID1."'"
            );
            $payee['payee1'] = $result1[0]->name;
            $payee['payee2'] = $this->getTierUpPayPalEmail();
        } 
        elseif( $allocation == 6 || $allocation == 7) {
                $payee['payee1'] = $this->getTierUpPayPalEmail();
                $payee['payee2'] = "";

        } elseif($allocation == 8) { 
            $payee['payee1'] = "Donation";
            $payee['payee2'] = "";
        }
        return $payee;
    }

    public function getTierUpPayPalEmail(){
        $activeBoardTwo = $this->user->getTierTwoActiveUserID();
        $uniqueID2 = $activeBoardTwo->uniqueID;
        $boardID = $activeBoardTwo->board_id;

        $t2Recipients = $this->user->getDynamicTableByID('tier_one','t2_recipient',"'".$boardID."'");
        $t2RecipientCount = (empty($t2Recipients)) ? 0 : count($t2Recipients);

        if($t2RecipientCount < 3) {
            $payee = "Community"; 
        }
        elseif($t2RecipientCount < 17) {
            $activeBoardUserT2 = $this->user->getDynamicTableByID(
                'users','uniqueID',"'".$uniqueID2."'"
            );
            $payee = $activeBoardUserT2[0]->name;
        }
        elseif($t2RecipientCount == 17) {
            $payee = "Donation";
        }else{
            $payee = $this->getTierUp3PayPalEmail();
        }

        return $payee;
    }

    function getTierUp3PayPalEmail() {
        $found_board = $this->user->getTierThreeActiveUserID();
        $uniqueID = $found_board->uniqueID;
        $board_id = $found_board->board_id;
        $resultSet2 = $this->user->getDynamicTableByID(
            'tier_one','t3_recipient',"'".$board_id."'"
        );
        $num = (empty($resultSet2)) ? 0 : count($resultSet2);

        if($num < 6) {
            $tierUpEmail = "Community";
        } elseif($num < 47) {
            $found_user = $this->user->getDynamicTableByID(
                'users','uniqueID',"'".$uniqueID."'"
            );
            $tierUpEmail = $found_user->paypal_email;
        } else {
            $tierUpEmail = "Donation";
        } 
        return $tierUpEmail;
    }

    
}
