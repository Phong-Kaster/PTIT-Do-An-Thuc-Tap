<?php 
    class OrdersController extends Controller
    {
        public function process()
        {
            $request_method = Input::method();
            if( $request_method === 'GET' )
            {
                $this->getLatestOrder();
            }
            else if( $request_method === 'POST')
            {
                //print_r($request_method);
                $this->modifyOrder();
            }
        }

        /**
         * @author Phong-Kaster
         * get the latest order whose status is still processing.
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
                        "user_id" => $order->user_id,
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
                            "id" => $element->id,
                            "product_id" => $element->product_id,
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
                    $receiver_phone = $AuthUser->get("receiver_phone");
                    $receiver_address = $AuthUser->get("receiver_address");
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
                                    "user_id" => $user_id,
                                    "receiver_phone" => $receiver_phone,
                                    "receiver_address" => $receiver_address,
                                    "receiver_name" => $receiver_name,
                                    "description" => $description,
                                    "status" => $status,
                                    "total" => $total,
                                    "create_at" => $create_at,
                                    "update_at" => $update_at
                            ));

                    $msg = "Order is created successfully !";

                    $data = array(
                        "id" => $id,
                        "user_id" => $user_id,
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


        private function modifyOrder(){
            /**Step 1 */
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");


            if( !isset($Route->params->id) ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }

            /**Step 2 - get the order  */
            $Order = Controller::model("Order", $Route->params->id);
            if( !$Order->isAvailable() ){
                $this->resp->msg = "This order is not available !";
                $this->jsonecho();
            }

            /**Step 2.1 - order is able to be modified when it's processing or packed */
            $invalid_status = ["being transported", "delivered", "cancel"];
            $current_status = $Order->get("status");
            if( in_array($current_status, $invalid_status)){
                $this->resp->msg = "This order can't be modified because it's ".$current_status;
                $this->jsonecho();
            }


            $required_fields = ["product_id"];
            foreach($required_fields as $field){
                if(!Input::post($field)){
                    $this->resp->msg = "Missing field ".$field;
                    $this->jsonecho();
                }
            }


            /**Step 3*/
            $Product = Controller::model("Product", Input::post("product_id"));
            if( !$Product->isAvailable() ){
                $this->resp->msg = "Product doesn't exist !";
                $this->jsonecho();
            }


            /**Step 4 - declare product_id, quantity, product_price to calculate total & quantity  */
            $product_id = Input::post("product_id");
            $quantity = Input::post("quantity") ? (int)Input::post("quantity") : 0;
            $product_price = (int)$Product->get("price");
            $data = [];//store returned data;


            /**Step 5 - check product_id exists in current order ? */
            $query = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                    ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id","=",$Route->params->id)
                    ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id","=",$product_id)
                    ->select([
                        DB::raw(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".quantity"),
                        DB::raw(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".price")
                    ]);
            
            
            $result = $query->get();

            /** if product_id exits but quantity = 0 => delete */
            if( count($result) > 0 && $quantity == 0)
            {
                $queryUpdate = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id","=",$Route->params->id)
                ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id","=",$product_id)
                ->delete();

                $data = array(
                    "order_id" => $Route->params->id,
                    "product_id" => (int)$product_id,
                    "quantity" =>  (int)$quantity,
                    "product_price" => (int)$Product->get("price")
                );
            }
            /** if product_id have existed then increase its quantity one unit */
            else if( count($result) > 0 && $quantity > 0){
                
                $newQuantity = $quantity;// calculate current quantity with received quantity 
                $newPrice = $newQuantity*$product_price;// new quantity multiple with product_price

                $queryUpdate = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                            ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id","=",$Route->params->id)
                            ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id","=",$product_id)
                            ->update(array(
                                "quantity" => $newQuantity,
                                "price" => $newPrice
                            ));
                $data = array(
                    "order_id" => $Route->params->id,
                    "product_id" =>(int) $product_id,
                    "quantity" =>  (int)$newQuantity,
                    "price" => (int)$newPrice
                );
            }
             /** if product_id doesn't exist then create a new order's content */
            else
            {
                $OrdersContent = Controller::model("OrdersContent");
                $OrdersContent->set("order_id", $Route->params->id)
                            ->set("product_id", $product_id)
                            ->set("price", $product_price*$quantity)
                            ->set("quantity", $quantity)
                            ->save();

                $data = array(
                    "order_id" =>$OrdersContent->get("order_id"),
                    "product_id" =>(int)$OrdersContent->get("product_id"),
                    "quantity" =>(int)$OrdersContent->get("quantity"),
                    "price" =>(int)$OrdersContent->get("price"),
                );
            }


            /**Step 6 - update the "total" field of the order  */
            $queryTotalAmount = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                    ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id","=",$Route->params->id)
                    ->select([
                        DB::raw("SUM( ".TABLE_PREFIX.TABLE_ORDERS_CONTENT.".price) as total_amount")
                    ]);
            
            
            $resultTotalAmount = $queryTotalAmount->get();
            $totalAmount = $resultTotalAmount[0]->total_amount;
           

            $Order->set("update_at", date("Y-m-d H:i:s"))
                ->set("total", $totalAmount)
                ->save();
            try 
            {     
                $this->resp->result = 1;
                $this->resp->msg = "Order content have been modified successfully !";
                $this->resp->total = $Order->get("total");
                $this->resp->update_at = $Order->get("update_at");
                $this->resp->data = $data;
            } 
            catch (\Exception $ex) {
                //throw $th;
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }
    }
?>