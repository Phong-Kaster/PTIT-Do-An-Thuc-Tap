<?php 
    class OrderLatestController extends Controller
    {
        public function process()
        {
            $request_method = Input::method();

            if( $request_method === 'GET')
            {
                $this->getLatestOrder();
            }
        }



        /**
         * @author Phong-Kaster
         * get the latest order whose status is still "processing".
         * if there is no any order like this, create a new order for the user.
         * 
         * data is an array storing order's information
         * content is an array storing order's content
         */
        private function getLatestOrder(){
            $AuthUser = $this->getVariable("AuthUser");
            $this->resp->result = 0;
            $data = [];
            $content = [];

            /**Users doesn't have any account */
            if(!$AuthUser)
            {
                $this->resp->msg = "There is no authenticated user !";
                $this->jsonecho();
            }


            /**Users have their own account */
            if($AuthUser)
            {
                /**Step 1 - check $AuthUser is active or not ? */
                if( !$AuthUser->get("active") ){
                    $this->resp->msg = "Your account is inactive !";
                    $this->jsonecho();
                }


                /**Step 2 - get user's latest order which has "processing" status */
                $query = DB::table(TABLE_PREFIX.TABLE_ORDERS)
                        ->where(TABLE_PREFIX.TABLE_ORDERS.".user_id", "=", $AuthUser->get("id"))
                        ->where(TABLE_PREFIX.TABLE_ORDERS.".status", "=", "processing")
                        ->limit(1)
                        ->orderBy("id","desc");
                $result = $query->get();
                /**if the order exists then pick it up */
                if( count($result) > 0 )
                {
                    $order = $result[0];
                    $msg = "Latest order is picked up successfully !";
                    $data = array(
                        "id" => $order->id,
                        "user_id" => (int)$AuthUser->get("id"),
                        "receiver_phone" => $order->receiver_phone,
                        "receiver_address" => $order->receiver_address,
                        "receiver_name" => $order->receiver_name,
                        "description" => $order->description,
                        "status" => $order->status,
                        "total" => (int)$order->total,
                        "create_at" => $order->create_at,
                        "update_at" => $order->update_at
                    );


                    /**and then get the order's content */
                    $orderContent = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                                    ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id", "=", $order->id)
                                    ->leftJoin(TABLE_PREFIX.TABLE_PRODUCTS, 
                                                TABLE_PREFIX.TABLE_PRODUCTS.".id", 
                                                "=", 
                                                TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id")
                                    ->select([
                                        TABLE_PREFIX.TABLE_ORDERS_CONTENT.".*",
                                        DB::raw(TABLE_PREFIX.TABLE_PRODUCTS.".name as product_name"),
                                        DB::raw(TABLE_PREFIX.TABLE_PRODUCTS.".price as product_price"),
                                    ]);
                    $result = $orderContent->get();

                    foreach($result as $element)
                    {
                        $product_avatar = getProductAvatar($element->product_id);

                        $content[] = array(
                            "id" => (int)$element->id,
                            "product_id" => (int)$element->product_id,
                            "product_name" => $element->product_name,
                            "product_price" => (int)$element->product_price,
                            "product_avatar" => $product_avatar,
                            "quantity" => (int)$element->quantity,
                            "price" => (int)$element->price
                        );
                    }
                }
                /**if the order does not exits then create a new order for this users */
                else
                {
                    $id = generateUUID();
                    $user_id = $AuthUser->get("id");
                    $receiver_phone = $AuthUser->get("phone");
                    $receiver_address = $AuthUser->get("address");
                    $receiver_name = $AuthUser->get("first_name")." ".$AuthUser->get("last_name");
                    $description = "";
                    $status = "processing";
                    $total = 0;
                    $create_at = date("Y-m-d H:i:s");
                    $update_at = date("Y-m-d H:i:s");

                    $query = DB::table(TABLE_PREFIX.TABLE_ORDERS)
                            ->insert(
                                array(
                                    "id" => $id,
                                    "user_id" => (int)$user_id,
                                    "receiver_phone" => $receiver_phone,
                                    "receiver_address" => $receiver_address,
                                    "receiver_name" => $receiver_name,
                                    "description" => $description,
                                    "status" => $status,
                                    "total" => (int)$total,
                                    "create_at" => $create_at,
                                    "update_at" => $update_at
                            ));

                    $msg = "Order is created successfully !";

                    $data = array(
                        "id" => $id,
                        "user_id" => (int)$user_id,
                        "receiver_phone" => $receiver_phone,
                        "receiver_address" => $receiver_address,
                        "receiver_name" => $receiver_name,
                        "description" => $description,
                        "status" => $status,
                        "total" => (int)$total,
                        "create_at" => $create_at,
                        "update_at" => $update_at
                    );
                }
            }

            $this->resp->result = 1;
            $this->resp->msg = $msg;
            $this->resp->data = $data;
            $this->resp->content = $content;
            $this->jsonecho();
        }
    }
?>