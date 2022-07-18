<?php 
    class TestController extends Controller
    {
        public function process()
        {

            $query = DB::table(TABLE_PREFIX.TABLE_PRODUCTS)
                    ->where(TABLE_PREFIX.TABLE_PRODUCTS.".id", "=", 1)
                    ->select("*");

            $result = $query->get();

            foreach($result as $element){
                $avatar = $this->getAvatar($element->id);

                    $data[] = array(
                        "id"            => (int)$element->id,
                        "name"          => $element->name,
                        "remaining"     => (int)$element->remaining,
                        "manufacturer"  => $element->manufacturer,
                        "price"         => (int)$element->price,
                        "screen_size"   => (float)$element->screen_size,
                        "cpu"           => $element->cpu,
                        "ram"           => $element->ram,
                        "graphic_card"  => $element->graphic_card,
                        "rom"           => $element->rom,
                        "demand"        => $element->demand,
                        "content"       => $element->content,
                        "avatar"        => $avatar ? $avatar : UPLOAD_PATH."./default.png"
                    );
            }

            $this->resp->result = 1;
            $this->resp->data = $data;
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