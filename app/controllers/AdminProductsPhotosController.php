<?php
    class AdminProductsPhotosController extends Controller
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
         * get photos of a product
         */
        private function getAll(){
            /**Step 1 */
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");


            /**Step 2 */
            if( !$Route->params->id ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }


            /**Step 3 */
            $Product = Controller::model("Product", $Route->params->id);
            if( !$Product->isAvailable()){
                $this->resp->msg = "The product doesn't exist !";
                $this->jsonecho();
            }

            
            /**Step 4 */
            try 
            {
                $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
                            ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".product_id", 
                                    "=", $Product->get("id") );

                $result = $query->get();

                if( count($result) == 0){
                    $ProductsPhoto = Controller::model("ProductsPhoto");
                    $ProductsPhoto->set("product_id", $Route->params->id)
                                  ->set("path", "default.png")
                                  ->set("is_avatar",1)
                                  ->save();
                }

                $data = [];

                foreach($result as $element){
                    $data[] = array(
                        "id" => $element->id,
                        "path" => UPLOAD_PATH."/".$element->path,
                        "is_avatar" => $element->is_avatar
                    );
                }

                $this->resp->result = 1;
                $this->resp->product_id = $Product->get("id");
                $this->resp->data = $data;
            } 
            catch (\Exception $ex) {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }

        
        /**
         * @author Phong-Kaster
         * add photo to a product
         */
        private function save(){
            /**Step 1 */
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");

            if( !$Route->params->id ){
                $this->resp->msg = "ID is required !";
                $this->jsonecho();
            }

            $is_avatar = Input::post("is_avatar") ? Input::post("is_avatar") : 0;


            $Product = Controller::model("Product", $Route->params->id);
            if( !$Product->isAvailable()){
                $this->resp->msg = "The product doesn't exist !";
                $this->jsonecho();
            }

            /**Step 2 - check if file is received or not */
            if (empty($_FILES["file"]) || $_FILES["file"]["size"] <= 0) {
                $this->resp->msg = __("Photo is not received!");
                $this->jsonecho();
            }

            
            /**Step 3 - check filename extension */
            $ext = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
            $allow = ["jpeg", "jpg", "png"];
            if (!in_array($ext, $allow)) {
                $this->resp->msg = __("Only ".join(",", $allow)." files are allowed");
                $this->jsonecho();
            }

            /**Step 4 - upload file */
            $date = new DateTime();
            $timestamp = $date->getTimestamp();
            $tempname = "product_".$Product->get("id")."_".$timestamp;
            $temp_dir = UPLOAD_PATH;
            if (!file_exists($temp_dir)) {
                mkdir($temp_dir);
            }
            
            $filepath = $temp_dir . "/" . $tempname . "." .$ext;
            if (!move_uploaded_file($_FILES["file"]["tmp_name"], $filepath)) {
                $this->resp->msg = __("Oops! An error occured. Please try again later!");
                $this->jsonecho();
            }

            
            /**Step 5 - create a record to the product */
            $ProductsPhoto = Controller::model("ProductsPhoto" );
            $ProductsPhoto->set("product_id", $Route->params->id)
                        ->set("path", $tempname)
                        ->set("is_avatar", $is_avatar )
                        ->save();



            $this->resp->result = 1;
            $this->resp->msg = __("Upload successful");
            $this->resp->image = $tempname . "." .$ext;
            $this->jsonecho();
        }
    }
?>