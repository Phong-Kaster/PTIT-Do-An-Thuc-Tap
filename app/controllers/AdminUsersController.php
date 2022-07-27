<?php
/**
 * Users Controller
 */
class AdminUsersController extends Controller
{
    /**
     * Process
     */
    public function process()
    {
        $AuthUser = $this->getVariable("AuthUser");

        // Authentication
        if (!$AuthUser){
            header("Location: ".APPURL."/login");
            exit;
        }
        else if (!$AuthUser->isAdmin()) 
        {
            header("Location: ".APPURL."/dashboard");
            exit;
        }

        
        // handle request
        $request_method = Input::method();
        if($request_method === 'POST')
        {
            $this->save();
        }
        else if($request_method === 'GET')
        {
           $this->getAll();
        }
    }

    /**
     * @author Phong
     * get all users information from database except the current authUser
     */
    private function getAll()
    {
        /**Step 1 */
        $AuthUser = $this->getVariable("AuthUser");
        $this->resp->result = 0;

        $search = Input::get("search");
        $start = Input::get("start");
        $length = Input::get("length");
        $order = Input::get("order");
        $draw = Input::get("draw");

        if($draw){
            $this->resp->draw = $draw; 
        }
           
        $data = [];

        print_r($AuthUser->get("id"));
        try 
        {
            $query = DB::table(TABLE_PREFIX.TABLE_USERS)
                        ->whereNot(TABLE_PREFIX.TABLE_USERS.".id", "=", $AuthUser->get("id"))
                        ->select([
                            "id",
                            "email",
                            "password",
                            "first_name",
                            "last_name",
                            "phone",
                            "address",
                            "role",
                            "active",
                            "create_at",
                            "update_at"
                        ]);

            $search_query = trim((string)$search);
            if($search_query){
                $query->where(function($q) use($search_query)
                    {
                        $q->where(TABLE_PREFIX.TABLE_USERS.".first_name", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_USERS.".last_name", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_USERS.".address", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_USERS.".role", 'LIKE', $search_query.'%')
                        ->orWhere(TABLE_PREFIX.TABLE_USERS.".email", 'LIKE', $search_query.'%');
                    });     
            }


            if($order && 
                isset($order["column"]) && 
                isset($order["dir"]) )
            {
                $sort =  in_array($order["dir"],["asc","desc"]) ? $order["dir"] : "desc";
                $column_name = trim($order["column"]) != "" ? trim($order["column"]) : "id";
                $query->orderBy($column_name, $sort);
            }  
            
            $res = $query->get();
            $count = count($res);
            $this->resp->quantity = $count;

            $query->limit($length ? $length : 10)->offset($start ? $start : 0);

            foreach($res as $r)
            {
                $data[] = array(
                    "id"=> (int)$r->id,
                    "email" => $r->email,
                    "role" => $r->role,
                    "first_name" => $r->first_name,
                    "last_name" => $r->last_name,
                    "phone" => $r->phone,
                    "address" => $r->address,
                    "active" => (bool)$r->active,
                    "create_at" => $r->create_at,
                    "update_at" => $r->update_at
                );
            }
            
            $this->resp->result = 1;
            $this->resp->data = $data;
            
        } 
        catch (\Exception $ex) 
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();
    }


    /**
     * @author Phong
     * add a new user
     */
    private function save()
    {
        /**Step 1 */
        $this->resp->result = 0;
        $AuthUser = $this->getVariable("AuthUser");

        /**Step 2 */
        $required_fields = ["email", "first_name", "last_name", "role" ];
        foreach( $required_fields as $field)
        {
            if( !Input::post($field) )
            {
                $this->resp->msg = __("Missing a required field: ".$field);
                $this->jsonecho();
            }
        }

        // Step 2.1
        if (!filter_var(Input::post("email"), FILTER_VALIDATE_EMAIL)) {
            $this->resp->msg = __("Email is not valid.");
            $this->jsonecho();
        }

        // Step 2.2
        $activeStatus = Input::post("active") == "true" ? 1 : 0;

        // Step 2.3
        $validAccount_type = ["admin", "member"];
        $account_type = Input::post("role");
        if( !in_array( $account_type, $validAccount_type) )
        {
            $account_type = "member";
        }

        // Step 2.4
        $User = Controller::model("User", Input::post("email"));
        if( $User->isAvailable() )
        {
            $this->resp->msg = __("The email is used by someone !");
            $this->jsonecho();
        }

        $query = DB::table(TABLE_PREFIX.TABLE_USERS)
                ->where(TABLE_PREFIX.TABLE_USERS.".phone", "=", Input::post("phone"));
        
        $result = $query->get();
        if(count($result) > 0)
        {
            $this->resp->msg = __("This phone number is used by someone !");
            $this->jsonecho();
        }

        /**Step 3 */
        
        $defaultPassword = "123456";
        $hashPassword = password_hash( $defaultPassword, PASSWORD_DEFAULT);

        /**Step 4 */
        $User = Controller::model("User");
            $User->set("email",        Input::post("email") )
                ->set("password",     $hashPassword)
                ->set("first_name",    Input::post("first_name") )
                ->set("last_name",     Input::post("last_name") )
                ->set("phone",         Input::post("phone") )
                ->set("address",       Input::post("address") )
                ->set("role", Input::post("role"))
                ->set("active",    $activeStatus)
                ->set("create_at",         date("Y-m-d H:i:s"))
                ->set("update_at",         date("Y-m-d H:i:s"))
                ->save();

        
        try 
        {
            //code...
            \Email::sendNotification("new-user", ["user" => $User, 'password' => $defaultPassword ]);
        } 
        catch (\Exception $ex) 
        {
            $this->resp->msg = $ex->getMessage();
            $this->jsonecho();
        }
        $this->resp->result = 1;
        $this->resp->msg = __("User is created successfully !");
        $this->resp->data = array(
            "email" => $User->get("email"),
            "first_name" => $User->get("first_name"),
            "last_name" => $User->get("last_name"),
            "phone" => $User->get("phone"),
            "address" => $User->get("address"),
            "role" => $User->get("role"),
            "active" => $User->get("active"),
            "create_at" => $User->get("create_at"),
            "update_at" => $User->get("update_at")
        );
        $this->jsonecho();
    }
}