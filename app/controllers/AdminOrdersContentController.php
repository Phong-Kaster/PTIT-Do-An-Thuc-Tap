<?php 
    class AdminOrdersContentController extends Controller{
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
                $this->getOrderContent();
            }
            else if( $request_method === 'POST'){
                $this->save();
            }
            else if ($request_method ==='PUT'){

            }
            else if( $request_method === 'DELETE'){

            }
        }


        /**
         * @author Phong-Kaster
         * get content of an order
         */
        private function getOrderContent(){
            /**Step 1 */
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");


            if( !$Route->params->id ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }

            /**Step 2 - get the product  */
            $Order = Controller::model("Order", $Route->params->id);
            if( !$Order->isAvailable() ){
                $this->resp->msg = "This order is not available !";
                $this->jsonecho();
            }


            /**Step 3 */
            try 
            {
                //code...
                $query = DB::table(TABLE_PREFIX.TABLE_ORDERS_CONTENT)
                        ->join(TABLE_PREFIX.TABLE_PRODUCTS,
                                TABLE_PREFIX.TABLE_PRODUCTS.".id",
                                "=",
                                TABLE_PREFIX.TABLE_ORDERS_CONTENT.".product_id")
                        ->where(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".order_id", "=", $Route->params->id )
                        ->select([
                            TABLE_PREFIX.TABLE_ORDERS_CONTENT.".*",
                            DB::raw(TABLE_PREFIX.TABLE_PRODUCTS.".name as product_name"),
                            DB::raw(TABLE_PREFIX.TABLE_PRODUCTS.".price as product_price"),
                            DB::raw(TABLE_PREFIX.TABLE_ORDERS_CONTENT.".price as price")
                        ]);

                $result = $query->get();

                $data = [];

                foreach($result as $element){
                    $data[] = array(
                        "id" => $element->id,
                        "product_id" => $element->product_id,
                        "product_name" => $element->product_name,
                        "product_avatar" => $this->getAvatar($element->id),
                        "product_price" => $element->product_price,
                        "quantity" => $element->quantity,
                        "price" => $element->price
                    );
                }

                $this->resp->result = 1;
                $this->resp->data = $data;
            } 
            catch (\Exception $ex) 
            {
                //throw $th;
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }


        /**
         * @author Phong-Kaster
         * price is calculated by product_price * quantity;
         * this price is the amount of money with one product
         */
        private function save(){
            /**Step 1 */
            $Route = $this->getVariable("Route");
            $this->resp->result = 0;

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


            $required_fields = ["product_id", "quantity"];
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
            $quantity = Input::post("quantity");
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
            /** if product_id have existed then increase its quantity one unit */
            if( count($result) > 0){
                
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
                    "product_id" => $product_id,
                    "quantity" =>  $newQuantity,
                    "price" => $newPrice
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
                    "id" => $OrdersContent->get("id"),
                    "order_id" =>$OrdersContent->get("id"),
                    "product_id" =>$OrdersContent->get("product_id"),
                    "quantity" =>$OrdersContent->get("quantity"),
                    "price" =>$OrdersContent->get("price"),
                );
            }


            /**************CALCULATE THIS ORDER'S TOTAL*************** */
            /** #CODE..... */



            try 
            {     
                $this->resp->result = 1;
                $this->resp->msg = "Order content is created successfully !";
                $this->resp->data = $data;
            } 
            catch (\Exception $ex) {
                //throw $th;
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }


        /**
         * @author Phong-Kaster
         * this function is used to get avatar for products
         */
        private function getAvatar($id){
            if( !$id ){
                return;
            }

            $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
            ->join(TABLE_PREFIX.TABLE_PRODUCTS,
                        TABLE_PREFIX.TABLE_PRODUCTS.".id",
                        "=",
                        TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".product_id")
            ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".is_avatar","=", 1)
            ->where(TABLE_PREFIX.TABLE_PRODUCTS.".id","=", $id)
            ->select([
                TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".path"
            ]);
            $res = $query->limit(1)->get();
            
            $tempres = [];
            $result = "";

            if( count($res) > 0 ){
                foreach($res as $r){
                    $tempres[] = $r->path;
                }

                $result = UPLOAD_PATH."/".$tempres[0];
            }
            else{
                $result = UPLOAD_PATH."/default.png";
            }



            return $result;
        }
    }
?>