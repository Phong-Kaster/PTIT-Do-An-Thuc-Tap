<?php 
    class CategoryController extends Controller
    {
        public function process()
        {
            $request_method = Input::method();
            $Route = $this->getVariable("Route");


            if( $request_method === 'GET' & isset($Route->params->id))
            {
                $this->getProductsByCategoryId();
            }
        }

        private function getProductsByCategoryId()
        {
            /**Step 1 - declare variable */
            $this->resp->result = 0;
            $Route = $this->getVariable("Route");
            
            if( !isset($Route->params->id) )
            {
                $this->resp->msg = "ID is required";
                $this->jsonecho();
            }


            $categoryId = $Route->params->id;
            $quantity = 0;
            $products = [];

        
            try 
            {
                /** Step 2 - query to get category's information */
                $category = Controller::model("Category", $Route->params->id);
                if( !$category->isAvailable() )
                {
                    $this->resp->msg = "Can find any category with this id !";
                    $this->jsonecho();
                }
                



                /** Step 3 - query to get all products relate to categoryId */
                $query = DB::table(TABLE_PREFIX.TABLE_CATEGORIES)
                        ->where(TABLE_PREFIX.TABLE_CATEGORIES.".id", "=", $categoryId)
                        ->leftJoin(TABLE_PREFIX.TABLE_PRODUCT_CATEGORY,
                                    TABLE_PREFIX.TABLE_PRODUCT_CATEGORY.".category_id",
                                        "=",
                                    TABLE_PREFIX.TABLE_CATEGORIES.".id")
                        ->leftJoin(TABLE_PREFIX.TABLE_PRODUCTS,
                                    TABLE_PREFIX.TABLE_PRODUCTS.".id",
                                    "=",
                                    TABLE_PREFIX.TABLE_PRODUCT_CATEGORY.".product_id")
                        ->select([
                            TABLE_PREFIX.TABLE_PRODUCTS.".*"
                        ]);
                    
                $result = $query->get();
                $quantity = count($result);

                foreach($result as $element)
                {

                    $avatar = getProductAvatar($element->id);

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
                $this->resp->category = array(
                    "id"            => (int)$category->get("id"),
                    "name"          => $category->get("name"),
                    "description"   => $category->get("description"),
                    "position"      => $category->get("position"),
                    "parent_id"     => (int)$category->get("parent_id"),
                    "slug"          => $category->get("slug")
                );
                $this->resp->quantity = $quantity;
                $this->resp->products = $data;
                
            } 
            catch (\Exception $ex) 
            {
                $this->resp->msg = $ex->getMessage();
            }
            $this->jsonecho();
        }
    }
?>