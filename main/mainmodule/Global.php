<?php

class GlobalMethods {
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function executeQuery($sql){
        $data = array();
        $errmsg = "";
        $code = 0;

        try{
            if($result = $this->pdo->query($sql)->fetchAll()){
                foreach($result as $record){
                    array_push($data, $record);
                }
                $code = 200;
                return array("code"=>$code, "data"=> $data);
            }
            else{
                $errmsg = 'No data found';
                $code = 404;
            }
        }
        catch(\PDOException $e){
           $errmsg = $e->getMessage();
           $code = 403;
        }
        return array("code"=> $code, "errmsg"=>$errmsg);
    }

    public function returnPayload($payload, $remarks, $message, $code){
        $status = array("remarks"=>$remarks, "message"=>$message);
        http_response_code($code);
        return array("status"=>$status, "payload"=>$payload, "timestamp"=>date_create());
    }


    public function insert($table_name, $data){
        $fields = [];
        $values = [];

        foreach($data as $key => $value){
            array_push($fields, $key);
            array_push($values, $value);
        }

        try{
            $counter = 0;
            $sql_str = "INSERT INTO $table_name (";

            foreach($fields as $value){
                $sql_str .= $value;
                $counter++;
                if($counter < count($fields)){
                    $sql_str .= ", ";
                }
            }

            $sql_str .= ") VALUES (" . str_repeat('?, ', count($values) - 1) . "?)";
            $sql = $this->pdo->prepare($sql_str);
            $sql->execute($values);
            return array("code"=>200, "remarks"=>"success");
        }
        catch(Exception $e){
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code"=>$code, "errmsg"=>$errmsg);
   
    }


    public function update($table_name, $data, $condition_string){
        $fields = [];
        $values = [];

    foreach($data as $key => $value){
        array_push($fields, $key);
        array_push($values, $value);
    }

    $sql = "UPDATE $table_name SET " . implode(", ", $fields);

    if (!empty($condition_string)) {
        $sql .= " WHERE $condition_string";
    }

    try {
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        return array("code" => 200, "remarks" => "success");
    } catch (\PDOException $e) {
        $errmsg = $e->getMessage();
        $code = 403; // Adjust error code as needed
    } catch (\Exception $e) {
        $errmsg = $e->getMessage();
        $code = 403; // Adjust error code as needed
    }
    return array("code" => $code, "errmsg" => $errmsg);
}
    

        /*$sql = "UPDATE $table_name SET stud_id = '$data->stud_id',
        stud_fname = '$data->stud_fname',  stud_email = '$data->stud_email',
        stud_role = '$data->stud_role' WHERE stud_id = '$data->stud_id' ";         

        try{
            $sql = $this->pdo->prepare($sql);
            $sql->execute();
            return array("code"=>200, "remarks"=>"success");
        }
        catch(Exception $e){
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code"=>$code, "errmsg"=>$errmsg);

    }*/
    

    public function delete($table_name, $condition_string){

        $sql = "DELETE FROM $table_name ";

        if($condition_string!=null){
            $sql .= " WHERE {$condition_string}";
        }
        try {
            $sql = $this->pdo->prepare($sql);
            $sql->execute();
            return array("status code"=>200, "remarks"=>"deleted successfully!");
        }
        catch(Exception $e){
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code"=>$code, "errmsg"=>$errmsg);
    }

    public function bookConsultation($data) {

        try {
            $sql = "INSERT INTO consultations (student_number, first_name, last_name, middle_name, email, college_program, consultation_details) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data->studentNumber, $data->firstName, $data->lastName, $data->middleName, $data->email, $data->course, $data->consultationDetails]);
    
            if ($stmt->rowCount() > 0) {
                return ['status' => 200, 'message' => 'Consultation booked successfully'];
            } else {
                return ['status' => 500, 'message' => 'Failed to book consultation'];
            }
        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            return ['status' => 500, 'message' => 'Internal server error'];
        }
    }

    public function generateSecretKey($length = 32){
        return bin2hex(random_bytes($length));
    }

    function sendResponse($data, $statusCode)
    {
        return array("status" => "success", "data" => $data, "statusCode" => $statusCode);
    }

    function sendErrorResponse($message, $statusCode)
    {
        return array("status" => "error", "message" => $message, "statusCode" => $statusCode);
    }


    /*public function update_cook($table_name, $data, $condition_string){

        $sql = "UPDATE $table_name SET name = '$data->name',  contact = '$data->contact',
        email = '$data->email' WHERE cook_id = '$data->cook_id' ";         

        try{
            $sql = $this->pdo->prepare($sql);
            $sql->execute();
            return array("code"=>200, "remarks"=>"success");
        }
        catch(Exception $e){
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code"=>$code, "errmsg"=>$errmsg);
    }

    public function update_user($table_name, $data, $condition_string){

        $sql = "UPDATE $table_name SET name = '$data->name', contact_number = '$data->contact_number', email = '$data->email' WHERE user_id = '$data->user_id' ";         

        try{
            $sql = $this->pdo->prepare($sql);
            $sql->execute();
            return array("code"=>200, "remarks"=>"success");
        }
        catch(Exception $e){
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code"=>$code, "errmsg"=>$errmsg);
    }
        
     public function update_item($table_name, $data, $condition_string){

        $sql = "UPDATE $table_name SET price = '$data->price',  item_name = '$data->item_name' WHERE item_id = '$data->item_id' ";         

        try{
            $sql = $this->pdo->prepare($sql);
            $sql->execute();
            return array("code"=>200, "remarks"=>"success");
        }
        catch(Exception $e){
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code"=>$code, "errmsg"=>$errmsg);
    }

    public function delete_item($table_name, $data, $condition_string){

        $sql = "UPDATE $table_name SET is_removed = '$data->is_removed' WHERE item_id = '$data->item_id' ";         

        try{
            $sql = $this->pdo->prepare($sql);
            $sql->execute();
            return array("code"=>200, "remarks"=>"success");
        }
        catch(Exception $e){
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code"=>$code, "errmsg"=>$errmsg);
    }

    public function restore_item($table_name, $data, $condition_string){

        $sql = "UPDATE $table_name SET is_removed = '$data->is_removed' WHERE item_id = '$data->item_id' ";         

        try{
            $sql = $this->pdo->prepare($sql);
            $sql->execute();
            return array("code"=>200, "remarks"=>"success");
        }
        catch(Exception $e){
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code"=>$code, "errmsg"=>$errmsg);
    }*/
    
}
?>