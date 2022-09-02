<?php 
    class AdminOrdersController extends Controller
    {

        public function process()
        {
            $AuthUser = $this->getVariable("AuthUser");
            //Auth
            if (!$AuthUser)
            {
                header("Location: ".APPURL."/login");
                exit;
            }
            else if( !$AuthUser->isAdmin() )
            {
                header("Location: ".APPURL."/dashboard");
                exit;
            }
            

            $request_method = Input::method();

            if($request_method === 'GET'){
                $this->getAll();
            }
            else if( $request_method === 'POST'){
                $this->save();
            }
        }

        /**
         * @author Phong-Kaster
         * get all orders 
         */
        private function getAll(){
            /**Step 1 */
            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");


            /**Step 1.1 - get basic filter condition */
            $order          = Input::get("order");
            $search         = Input::get("search");
            $length         = Input::get("length") ? (int)Input::get("length") : 10;
            $start          = Input::get("start") ? (int)Input::get("start") : 0;

            /**Step 2 */
            $data = [];
            try {
                $query = DB::table(TABLE_PREFIX.TABLE_ORDERS)
                        ->where(TABLE_PREFIX.TABLE_ORDERS.".total", ">", 0)
                        ->select("*");

                /**Step 2.1 - search filter - receiver_name | receiver_phone | receiver_address | description*/
                $search_query = trim( (string)$search );
                if($search_query){
                    $query->where(function($q) use($search_query)
                    {
                        $q->where(TABLE_PREFIX.TABLE_ORDERS.".receiver_name", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_ORDERS.".receiver_phone", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_ORDERS.".receiver_address", "LIKE", $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_ORDERS.".status", "LIKE", $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_ORDERS.".description", "LIKE", $search_query.'%');
                    }); 
                }

                /**Step 2.2 - orderBy filter */
                if( $order && isset($order["column"]) && isset($order["dir"]))
                {
                    $type = $order["dir"];
                    $validType = ["asc","desc"];
                    $sort =  in_array($type, $validType) ? $type : "desc";


                    $column_name = trim($order["column"]) != "" ? trim($order["column"]) : "id";
                    $column_name = str_replace(".", "_", $column_name);
    

                    if(in_array($column_name, ["receiver_name", "receiver_description", "receiver_phone"])){
                        $query->orderBy(DB::raw($column_name. " * 1"), $sort);
                    }else{
                        $query->orderBy($column_name, $sort);
                    }
                }
                else 
                {
                    $query->orderBy("update_at", "desc");
                } 

                /**Step 2.3 - length filter * start filter*/
                $query->limit($length ? $length : 10)->offset($start ? $start : 0);
                $res = $query->get();
                $quantity = count($res);

                /**Step 3 */
                foreach($res as $element){
                    $data[] = array(
                        "id" => $element->id,
                        "user_id" => (int)$element->user_id,
                        "receiver_phone" => $element->receiver_phone,
                        "receiver_address" => $element->receiver_address,
                        "receiver_name" => $element->receiver_name,
                        "description" => $element->description,
                        "status" => $element->status,
                        "total" => (int)$element->total,
                        "create_at" => $element->create_at,
                        "update_at" => $element->update_at
                    );
                }

                $this->resp->result = 1;
                $this->resp->quantity = $quantity;
                $this->resp->data = $data;

            } catch (\Exception $ex) {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }

        /**
         * @author Phong-Kaster
         * create an order
         * 
         * generateUUID is from helper/common.helper.php
         */
        private function save(){

            /**Step 1 - check if missing some required field: receiver_phone, 
             * receiver_name, receiver_address */
            $this->resp->result = 0;

            

            $id = generateUUID();
            $user_id            = Input::post("user_id") ? Input::post("user_id") : NULL;
            $receiver_phone     = Input::post("receiver_phone");
            $receiver_address   = Input::post("receiver_address");
            $receiver_name      = Input::post("receiver_name");
            $description        = Input::post("description");
            $create_at          = date("Y-m-d H:i:s");
            $update_at          = date("Y-m-d H:i:s");
            $status             = "processing";
            $total              = 0;


            /**Step 2 - no need to check duplicate with id */
            $required_fields = ["receiver_phone","receiver_address","receiver_name"];

            foreach($required_fields as $field){
                if( !Input::post($field) ){
                    $this->resp->msg = "Missing field ".$field;
                    $this->jsonecho();
                }
            }

            /**Step 2.1 - check phone number */
            if( strlen($receiver_phone) < 10 ){
                $this->resp->msg = "Phone number has at least 10 number !";
                $this->jsonecho();
            }

            $phone_number_validation = isNumber($receiver_phone);
            if( !$phone_number_validation ){
                $this->resp->msg = "This is not a valid phone number. Please, try again !";
                $this->jsonecho();
            }

            /**Step 2.2 - check name - only letters and space */
            $name_validation = isVietnameseName($receiver_name);
            if( $name_validation != 1 ){
                $this->resp->msg = "Receiver name only has letters and space";
                $this->jsonecho();
            }


            $address_validation = isAddress($receiver_address);
            if( $address_validation != 1){
                $this->resp->msg = "Address only has letters, space & comma";
                $this->jsonecho();
            }

            /**Step 3 - create order with default status is pending 
             * valid status is pending | packing | delivered | cancel */
            $valid_status = ["processing", "verified", "packed", "being transported", "delivered", "cancel"];
            if( !in_array($status, $valid_status)){
                $this->resp->msg = "Status is not valid, only has processing, verified, packed, being transported, delivered, cancel";
                $this->jsonecho();
            }


            $query = DB::table(TABLE_PREFIX.TABLE_ORDERS)
                    ->insert(array(
                        "id" => $id,
                        "user_id" => $user_id,
                        "receiver_phone" => $receiver_phone,
                        "receiver_address" => $receiver_address,
                        "receiver_name" => $receiver_name,
                        "description" => $description,
                        "create_at" => $create_at,
                        "update_at" => $update_at,
                        "status" => "processing",
                        "total" => 0
                    ));


            /**Step final */
            $this->resp->result = 1;
            $this->resp->msg = "Order is created successfully!";
            $this->resp->data = array(
                "id" => $id,
                "user_id" => $user_id,
                "receiver_phone" => $receiver_phone,
                "receiver_address" => $receiver_address,
                "receiver_name" => $receiver_name,
                "description" => $description,
                "status"   => $status,
                "total"     => $total,
                "create_at" => $create_at,
                "update_at" => $update_at
            );
            $this->jsonecho();
        }
    }
?>