<?php 
    class TestController extends Controller
    {
        public function process()
        {

            $request_method = Input::method();
            if( $request_method == "GET")
            {
                
            }
            if($request_method === "POST")
            {
                $this->uploadFile();
            }
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


        private function uploadFile()
        {
            /**Step 1 */
            $this->resp->result = 0;
            $AuthUser = $this->getVariable("AuthUser");

            if (!$AuthUser)
            {
                $this->resp->msg = "There is no authenticated user!";
                $this->jsonecho();
            }

            $productId = Input::post("product_id");

            /**Step 2 - check if file is received or not */
            if (empty($_FILES["file"]) || $_FILES["file"]["size"] <= 0) {
                $this->resp->msg = "Photo is not received!";
                $this->jsonecho();
            }

            if( $_FILES["file"]["size"] > 5242880  )
            {
                $this->resp->msg = "Image greater than 5MB. Please, try again!";
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
            $tempname = "product_".$timestamp;
            $temp_dir = UPLOAD_PATH;
            if (!file_exists($temp_dir)) {
                mkdir($temp_dir);
            }
            
            $filepath = $temp_dir . "/" . $tempname . "." .$ext;
            if (!move_uploaded_file($_FILES["file"]["tmp_name"], $filepath)) {
                $this->resp->msg = __("Oops! An error occured. Please try again later!");
                $this->jsonecho();
            }

            /**Step 5 - if the uploaded photo is set as avatar, other photos will be not avatar */
            // if($is_avatar == 1){
            //     $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO)
            //             ->where(TABLE_PREFIX.TABLE_PRODUCTS_PHOTO.".product_id", "=", 1)
            //             ->update(array(
            //                 "is_avatar" => 0
            //             ));
            // }
            
            /**Step 6 - create a record to the product */
            $ProductsPhoto = Controller::model("ProductsPhoto" );
            $ProductsPhoto->set("product_id", 1)
                        ->set("path", $tempname.".".$ext)
                        ->set("is_avatar", 0 )
                        ->save();



            $this->resp->result = 1;
            $this->resp->msg = __("Upload successful");
            $this->resp->productId = $productId;
            $this->jsonecho();
        }
    }
?>