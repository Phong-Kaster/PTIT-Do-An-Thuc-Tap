<?php
/**
 * User Controller
 */
class AdminUserController extends Controller
{
    /**
     * Process
     */
    public function process()
    {
        $Route = $this->getVariable("Route");
        $AuthUser = $this->getVariable("AuthUser");

        // Auth
        if (!$AuthUser){
            header("Location: ".APPURL."/login");
            exit;
        }
        else if (!$AuthUser->isAdmin()) {
            header("Location: ".APPURL."/dashboard");
            exit;
        }


        $request_method = Input::method();
        if($request_method === 'PUT'){
            $this->save();
        }else if($request_method === 'GET'){
            $this->getById();
        }else if($request_method === 'DELETE'){
            $this->remove();
        }else if($request_method === 'PATCH'){
            $this->restore();
        }

    }

    /**
     * @author Phong
     * get an user by his/her id
     */
    private function getById()
    {
        /**Step 1 */
        $AuthUser = $this->getVariable("AuthUser");
        $Route = $this->getVariable("Route");
        $this->resp->result = 0;

        if( !isset($Route->params->id) )
        {
            $this->resp->msg = __("ID is required !");
            $this->jsonecho();
        }

        /**Step 2 */
        $User = Controller::model("User", $Route->params->id );
        if( !$User->isAvailable() )
        {
            $this->resp->msg = __("This user does not exist !");
            $this->jsonecho();
        }

        /**Step 3 */
        $this->resp->result = 1;
        $this->resp->data = array(
            "id"          => (int)$User->get("id"),
            "email"       => $User->get("email"),
            "first_name"   => $User->get("first_name"),
            "last_name"    => $User->get("last_name"),
            "phone" => $User->get("phone"),
            "address" => $User->get("address"),
            "active"    => (bool)$User->get("active"),
            "create_at" => $User->get("create_at"),
            "update_at" => $User->get("update_at")
        );
        $this->jsonecho();
    }

    /**
     * @author Phong
     * remove an user yet user is just deactivated
     */
    private function remove()
    {

        try 
        {
            //code...
            $AuthUser = $this->getVariable("AuthUser");
            $Route = $this->getVariable("Route");
            $this->resp->result = 0;

            if( !isset($Route->params->id) )
            {
                $this->resp->msg = __("ID is required !");
                $this->jsonecho();
            }

            $User = Controller::model("User", $Route->params->id);
            if( !$User->isAvailable() )
            {
                $this->resp->msg = __("This user does not exist !");
                $this->jsonecho();
            }

            if( $User->get("active") == 0 )
            {
                $this->resp->msg = __("This user was deactivate !");
                $this->jsonecho();
            }

            if (!$AuthUser->canEdit($User)) {
                $this->resp->msg = __("You don't have a permission to modify this user's data!");
                $this->jsonecho();   
            }

            if ($AuthUser->get("id") == $User->get("id")) {
                $this->resp->msg = __("You can not deactivate your own account!");
                $this->jsonecho();
            }

            $User->set("active", 0)
                 ->set("update_at", date("Y-m-d H:i:s"))
                 ->save();
            // $User->delete();

            $this->resp->result = 1;
            $this->resp->msg = __("User is deactivated !");
            $this->resp->data = array(
                "id"    => $User->get("id"),
                "email" => $User->get("email"),
                "first_name" => $User->get("first_name"),
                "last_name" => $User->get("last_name"),
                "role" => $User->get("role"),
                "active" => $User->get("active"),
                "create_at" => $User->get("create_at"),
                "update_at" => $User->get("update_at")
            );
            
        } 
        catch (\Exception $ex) 
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();
    }


    /**
     * @author Phong
     * change an user's information
     */
    private function save()
    {
        /**Step 1 */
        $AuthUser = $this->getVariable("AuthUser");
        $Route = $this->getVariable("Route");
        $this->resp->result = 0;

        
        if( !isset($Route->params->id) )
        {
            $this->resp->msg = __("ID is required !");
            $this->jsonecho();
        }


        /**Step 2 */
        $required_fields = ["first_name", "last_name", "role"];
        foreach( $required_fields as $field )
        {
            if( !Input::put($field) )
            {
                $this->resp->msg = __("Missing some required field ".$field);
                $this->jsonecho();
            }
        }


        // Step 2.2 - only admin can change user's active status
        $activeStatus = Input::put("active") == "true" ? 1 : 0;


        $query = DB::table(TABLE_PREFIX.TABLE_USERS)
                ->where(TABLE_PREFIX.TABLE_USERS.".phone", "=", Input::put("phone"));
        
        $result = $query->get();
        if(count($result) > 0)
        {
            $this->resp->msg = __("This phone number is used by someone !");
            $this->jsonecho();
        }


        // Step 2.3 - only admin can change user's role
        $validAccount_type = ["admin", "member"];
        $account_type = Input::put("role");
        if( !in_array( $account_type, $validAccount_type) )
        {
            $account_type = "member";
        }


        /**Step 3 */
        $User = Controller::model("User", $Route->params->id);
        if( !$User->isAvailable() )
        {
            $this->resp->msg = __("There isn't any user who have this email !");
            $this->jsonecho();
        }


        try 
        {        
            /**Step 4 */
            $User->set("first_name", Input::put("first_name") )
                ->set("last_name", Input::put("last_name") )
                ->set("phone", Input::put("phone") )
                ->set("address", Input::put("address") )
                ->set("role", $account_type)
                ->set("active", $activeStatus)
                ->set("update_at", date("Y-m-d H:i:s"))
                ->save();

                
            $this->resp->result = 1;
            $this->resp->msg = "User is modified successfully !";
            $this->resp->data = array(
                "id"    => $User->get("id"),
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

        } 
        catch (\Exception $ex) 
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();
    }

    /**
     * @author Hau
     * restore theo id
     */
    private function restore()
    {
        try 
        {
            $AuthUser = $this->getVariable("AuthUser");
            $Route = $this->getVariable("Route");
            $this->resp->result = 0;

            if( !isset($Route->params->id) )
            {
                $this->resp->msg = __("ID is required!");
                $this->jsonecho();
            }

            $User = Controller::model("User", $Route->params->id);
            if( !$User->isAvailable() )
            {
                $this->resp->msg = __("This user does not exist!");
                $this->jsonecho();
            }

            if( $User->get("active") == 1 )
            {
                $this->resp->msg = __("This user was activate!");
                $this->jsonecho();
            }

            if (!$AuthUser->canEdit($User)) {
                $this->resp->msg = __("You don't have a permission to modify this user's data!");
                $this->jsonecho();   
            }

            $User->set("active", 1)
                ->set("update_at", date("Y-m-d H:i:s"))
                 ->save();

            $this->resp->result = 1;
            $this->resp->msg = __("User is Activated!");
            $this->resp->data = array(
                "id"    => $User->get("id"),
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
            
        } 
        catch (\Exception $ex) 
        {
            $this->resp->msg = $ex->getMessage();
        }
        $this->jsonecho();
    }
}