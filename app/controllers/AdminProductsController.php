<?php
    class AdminProductsController extends Controller
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
         * this function get products information with/ without conditions
         * length is the quantity of records got
         * start is the record's position where we begin querying. For instance, 10 means we queries from 10th records.
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


            /**Step 1.2 - get advanced filter condition */
            $manufacturer   = Input::get("manufacturer");
            $priceFrom      = Input::get("priceFrom");
            $priceTo        = Input::get("priceTo");
            $screenSize     = Input::get("screenSize");
            $cpu            = Input::get("cpu");
            $ram            = Input::get("ram");
            $graphicCard    = Input::get("graphicCard");
            $rom            = Input::get("rom");
            $demand         = Input::get("demand");

            
            /**Step 2 */
            $data = [];
            try 
            {
                $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS)
                        ->select([
                            TABLE_PREFIX.TABLE_PRODUCTS.".*"
                        ]);

                
                /**Step 2.1 - search filter - name | manufacturer | cpu | demand | graphic_card*/
                $search_query = trim( (string)$search );
                if($search_query){
                    $query->where(function($q) use($search_query)
                    {
                        $q->where(TABLE_PREFIX.TABLE_PRODUCTS.".name", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_PRODUCTS.".manufacturer", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_PRODUCTS.".cpu", "LIKE", $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_PRODUCTS.".demand", "LIKE", $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_PRODUCTS.".graphic_card", 'LIKE', $search_query.'%');
                    }); 
                }


                /**Step 2.2 - manufacturer filter */
                if($manufacturer){
                    $query->where(TABLE_PREFIX.TABLE_PRODUCTS.".manufacturer", "LIKE", $manufacturer.'%');
                }


                /**Step 2.3 - priceFrom & priceTo */
                if($priceFrom){
                    $query->where(TABLE_PREFIX.TABLE_PRODUCTS.".price",">=",$priceFrom);
                }
                if($priceTo){
                    $query->where(TABLE_PREFIX.TABLE_PRODUCTS.".price","<=",$priceTo);
                }


                /**Step 2.4 - screenSize */
                if($screenSize){
                    $query->where(TABLE_PREFIX.TABLE_PRODUCTS.".screen_size","=",$screenSize);
                }


                /**Step 2.5 - cpu */
                if($cpu){
                    $query->where(TABLE_PREFIX.TABLE_PRODUCTS.".cpu","=",$cpu);
                }


                /**Step 2.6 - ram */
                if($ram){
                    $query->where(TABLE_PREFIX.TABLE_PRODUCTS.".ram","=",$ram);
                }


                /**Step 2.7 - graphic card */
                if($graphicCard){
                    $query->where(TABLE_PREFIX.TABLE_PRODUCTS.".graphic_card","=",$graphicCard);
                }


                /**Step 2.8 - rom */
                if($rom){
                    $query->where(TABLE_PREFIX.TABLE_PRODUCTS.".rom","=",$rom);
                }

                
                /**Step 2.9 - demand */
                if($demand){
                    $query->where(TABLE_PREFIX.TABLE_PRODUCTS.".demand","=",$demand);
                }


                /**Step 2.10 - orderBy filter */
                if( $order && isset($order["column"]) && isset($order["dir"]))
                {
                    $type = $order["dir"];
                    $validType = ["asc","desc"];
                    $sort =  in_array($type, $validType) ? $type : "desc";


                    $column_name = trim($order["column"]) != "" ? trim($order["column"]) : "id";
                    $column_name = str_replace(".", "_", $column_name);
    

                    if(in_array($column_name, ["amount"])){
                        $query->orderBy(DB::raw($column_name. " * 1"), $sort);
                    }else{
                        $query->orderBy($column_name, $sort);
                    }
                }
                else 
                {
                    $query->orderBy("id", "desc");    
                }  
                        

                /**Step 2.11 - length filter * start filter*/
                $query->limit($length ? $length : 10)->offset($start ? $start : 0);
                $res = $query->get();
                
                
                
                /**Step 3 */
                foreach($res as $element){

                    $avatar = $this->getAvatar($element->id);
                    /**this will be main solution when API go public 
                     * At the moment, however, we just send the name of image 
                     * to be suitable for Android application.
                     */
                    // $avatar = "$avatar ? $avatar : UPLOAD_URL."/default.png"";

                    $data[] = array(
                        "id"            => (int)$element->id,
                        "name"          => $element->name,
                        "remaining"     => (int)$element->remaining,
                        "manufacturer"  => $element->manufacturer,
                        "price"         => (int)$element->price,
                        "screen_size"   => $element->screen_size,
                        "cpu"           => $element->cpu,
                        "ram"           => $element->ram,
                        "graphic_card"  => $element->graphic_card,
                        "rom"           => $element->rom,
                        "demand"        => $element->demand,
                        "content"       => $element->content,
                        "avatar"        => $avatar ? $avatar : "default.png"
                    );
                }

                /**Step 4 - return */
                $this->resp->result = 1;
                $this->resp->quantity = count($res);
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
         * create a new product
         */
        private function save(){
            /**Step 1 */
            $this->resp->result = 0;

            $required_fields = [
                "name","price",
                "screen_size","cpu",
                "ram","graphic_card",
                "rom"
            ];

            foreach($required_fields as $field){
                if( !Input::post($field))
                {
                    $this->resp->msg = "Missing required field ".$field;
                    $this->jsonecho(); 
                }
            }

            /**Step 2 */
            /**Step 2.1 - name */
            $name = Input::post("name");


            $remaining = Input::post("remaining");
            if( !$remaining ){
                $remaining = 0;
            }


            /**Step 2.2 - manufacturer */
            $manufacturer = Input::post("manufacturer");
            if( is_numeric($manufacturer) ){
                $this->resp->msg = "Manufacturer can not be a number !";
                $this->jsonecho();
            }


            /**Step 2.3 - 0 < price < 200.000.000 */
            $price = (int)Input::post("price");
            if( $price < 0 || $price > 200000000 ){
                $this->resp->msg = "Price range is 0 < price < 200.000.000";
                $this->jsonecho(); 
            }


            /**Step 2.4 - screen_size */
            $screen_size = (float)Input::post("screen_size");
            if( $screen_size < 10){
                $this->resp->msg = "Screen size must greater than 10 inch !";
                $this->jsonecho();
            }


            /**Step 2.5 - cpu */
            $cpu = Input::post("cpu");


            /**Step 2.6 - ram */
            $ram = Input::post("ram");


            /**Step 2.7 - graphic card */
            $graphic_card = Input::post("graphic_card");

            
            /**Step 2.8 - rom */
            $rom = Input::post("rom");


            /**Step 2.9 - demand */
            $valid_demand = ["", "gaming","design","office","student","business","lightweight"];
            $demand = Input::post("demand");

            if( !in_array($demand, $valid_demand) ){
                $this->resp->msg = "There are 5 types of valid demand: gaming, design, office, student, business, lightweight";
                $this->jsonecho();
            }

            /**Step 2.10 - content */
            (string)$content = (String)Input::post("content");
            if( !$content ){
                $content = __("Laptop ".(string)$name." từ nhà sản xuất ".
                (string)$manufacturer." là một laptop hiện đại, đa dụng & rất thời trang !");
            }

            /**Step 3 - create */
            $Product = Controller::model("Product", $name);
            if( $Product->isAvailable() ){
                $this->resp->msg = "There is another product having the same name !";
                $this->jsonecho();
            }


            $Product->set("name", $name)
                    ->set("remaining", $remaining)
                    ->set("manufacturer", $manufacturer)
                    ->set("price", $price)
                    ->set("screen_size", $screen_size)
                    ->set("cpu", $cpu)
                    ->set("ram", $ram)
                    ->set("graphic_card", $graphic_card)
                    ->set("rom", $rom)
                    ->set("demand", $demand)
                    ->set("content", $content)
                    ->set("create_at", date("Y-m-d H:i:s"))
                    ->set("update_at", date("Y-m-d H:i:s"))
                    ->save();

                    
            /**Step 4 - result */
            $this->resp->result = 1;
            $this->resp->msg = "Product is created successfully";
            $this->resp->data = array(
                "name" =>         $Product->get("name"),
                "remaining" =>    $Product->get("remaining"),
                "manufacturer" => $Product->get("manufacturer"),
                "price" =>        $Product->get("price"),
                "screen_size" =>  $Product->get("screen_size"),
                "cpu" =>          $Product->get("cpu"),
                "ram" =>          $Product->get("ram"),
                "graphic_card" => $Product->get("graphic_card"),
                "rom" =>          $Product->get("rom"),
                "demand" =>       $Product->get("demand"),
                "content" =>      $Product->get("content"),
                "create_at" =>    $Product->get("create_at"),
                "update_at" =>    $Product->get("update_at")
            );
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


            /**this will be main solution when API go public 
             * At the moment, however, we just send the name of image 
             * to be suitable for Android application.
             */
            // $avatar = "$avatar ? $avatar : UPLOAD_URL."/default.png"";
            /**$result = UPLOAD_PATH."/default.png"; */
            if( count($res) > 0 ){
                foreach($res as $r){
                    $tempres[] = $r->path;
                }

                $result = $tempres[0];
            }
            else{
                $result = "default.png";
            }



            return $result;
        }

    }// END OF CLASS
?>