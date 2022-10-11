<?php 
    class OrdersController extends Controller
    {
        public function process()
        {
            $request_method = Input::method();


            if( $request_method === 'GET')
            {
                $this->getAll();
            }

        }


        /**
         * @author Phong-Kaster
         * list all orders of a auth user
         */
        private function getAll()
        {
            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");
            $data = [];

            if( !$AuthUser )
            {
                $this->resp->msg = "There is no authenticated user !";
                $this->jsonecho();
            }

            $order          = Input::get("order");
            $search         = Input::get("search");
            $length         = Input::get("length") ? (int)Input::get("length") : 10;
            $start          = Input::get("start") ? (int)Input::get("start") : 0;
            $status         = Input::get("status");


            try 
            {
                $query = DB::table(TABLE_PREFIX.TABLE_ORDERS)
                    ->where(TABLE_PREFIX.TABLE_ORDERS.".user_id", "=", $AuthUser->get("id"))
                    ->where(TABLE_PREFIX.TABLE_ORDERS.".total", ">", 0)
                    ->select("*");

                
                $search_query = trim( (string)$search );
                if($search_query){
                    $query->where(function($q) use($search_query)
                    {
                        $q->where(TABLE_PREFIX.TABLE_ORDERS.".receiver_name", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_ORDERS.".receiver_address", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_ORDERS.".description", 'LIKE', $search_query.'%');
                    }); 
                }



                if( $status )
                {
                    $valid_status = ["processing", "verified", "packed", "being transported", "delivered", "cancel"];
                    if( !in_array($status, $valid_status)){
                        $this->resp->msg = "Status is not valid, only has processing, verified, packed, being transported, delivered, cancel";
                        $this->jsonecho();
                    }


                    $query->where(TABLE_PREFIX.TABLE_ORDERS.".status", "=", $status);
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
                $result = $query->get();
                $quantity = count($result);

                foreach($result as $element)
                {
                    $data[] = array(
                        "id" => $element->id,
                        "user_id" => (int)$element->user_id,
                        "receiver_phone" => $element->receiver_phone,
                        "receiver_address" => $ element->receiver_address,
                        "receiver_name" => $element->receiver_name,
                        "description" => $element->description,
                        "status"   => $element->status,
                        "total"     => (int)$element->total,
                        "create_at" => $element->create_at,
                        "update_at" => $element->update_at
                    );
                }

                $this->resp->result = 1;
                $this->resp->quantity = $quantity;
                $this->resp->data = $data;
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            
            $this->jsonecho();
        }
    }
?>