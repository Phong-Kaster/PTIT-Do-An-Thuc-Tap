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

        }

        /**
         * @author Phong-Kaster
         * create a order
         * 
         * generateUUID is from helper/common.helper.php
         */
        private function save(){

            /**Step 1 - check if missing some required field: receiver_phone, 
             * receiver_name, receiver_address */
            $id = generateUUID();
            $user_id            = Input::post("user_id");
            $receiver_phone     = Input::post("receiver_phone");
            $receiver_address   = Input::post("receiver_address");
            $receiver_name      = Input::post("receiver_name");
            $description        = Input::post("description");
            $create_at          = date("Y-m-d H:i:s");
            $status             = Input::post("status");
            $total              = 0;


            /**Step 2 - no need to check duplicate with id */
            
            /**Step 3 - create order with default status is pending 
             * valid status is pending | packing | delivered | cancel */

             $query = DB::table(TABLE_PREFIX.TABLE_ORDERS)
                    ->insert(array(
                        "id" => $id,
                        "user_id" => $user_id,
                        "receiver_phone" => $receiver_phone,
                        "receiver_address" => $receiver_address,
                        "receiver_name" => $receiver_name,
                        "description" => $description,
                        "create_at" => $create_at,
                        "status" => "pending",
                        "total" => 0
                    ));


            /**Step final */
            $this->resp->result = 1;
            $this->resp->uuid = $id;
            $this->jsonecho();
        }
    }
?>