<?php

include '../Admin/database.php'; 
session_start();
class seller {
    public $email;
    public $pass;
    public function login($email, $pass) {
        $this->email = $email;
        $this->pass = $pass;
            $_SESSION['acct_email'] = $email;
            $_SESSION['acct_pass'] = $pass;
        
        
    }
}

class sign_up extends seller{
    public $password_hash;
    public $name;
    public $conn;
    public function __construct($db_connection){
        $this->conn = $db_connection;
    }

    public function add_seller($email, $pass, $name){
        $this->email = $email;
        $this->name = $name;
        $this->pass = password_hash($pass, PASSWORD_DEFAULT);
        $sql = "INSERT INTO seller_info (seller_email, seller_name, seller_pass)
        VALUES ('$email', '$name' , '$this->pass')";
        mysqli_query($this->conn, $sql);
        header("Location: sign_up_result.php");
    exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['acct_email'];
    $name = $_POST['acct_name'];
    $pass = $_POST['acct_pass'];

    $sql = $conn->prepare ("SELECT * FROM buyer_info WHERE buyer_email = ?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $result = $sql->get_result();
    if($result->num_rows > 0){
        echo "Email already existed. Please select another email.";
    }else{
    $add_seller = new sign_up($conn);
    
    $add_seller->add_seller($email, $pass, $name);
        echo "seller added";
    }
    $sql->close();
}

class log_verify extends seller{
    public $conn;
    public function __construct($db_connection){
        $this->conn = $db_connection;
    }
    public function verify($email, $pass){
    $this->email = $email;
    $this->pass = $pass;
    if ($prepare_query = mysqli_prepare($this->conn, "SELECT seller_pass FROM seller_info WHERE seller_email = ?")) {
        mysqli_stmt_bind_param($prepare_query, "s", $email);
        mysqli_stmt_execute($prepare_query);
        $result = mysqli_stmt_get_result($prepare_query);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($pass, $row['seller_pass'])) {
                echo "Login Successful";
                $_SESSION['acct_email'] = $this->email; 
                header("Location: seller_page.php");
                exit();
            } else {
                echo "Incorrect password";
            }
        } else {
            echo "Seller not found.";
        }
    }
}
}

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['acct_email'];
    $pass = $_POST['acct_pass'];
    $seller = new seller();
    $seller->login($email, $pass);

$login_verify = new log_verify($conn);
$login_verify->verify($email, $pass);
    
}
    



if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_POST['acct_email']) && !empty($_POST['acct_pass'])) {
        $sel_email = $_POST['acct_email'];
        $sel_pass = $_POST['acct_pass'];


        
    }

    if (isset($_SESSION['acct_email']) && !empty($_POST['product_name']) && !empty($_POST['product_desc']) &&
        !empty($_POST['product_price']) && !empty($_POST['product_quantity']) && isset($_FILES['file_pic'])) {
        
        $product_name = $_POST['product_name'];
        $product_desc = $_POST['product_desc'];
        $product_price = $_POST['product_price'];
        $product_quantity = $_POST['product_quantity'];
        $product_pic = $_FILES['file_pic']['name'];

        $target_dir = "../uploads/";
        $target_file = $target_dir . basename($_FILES['file_pic']['name']);
        
        if (move_uploaded_file($_FILES["file_pic"]["tmp_name"], $target_file)) {
   
            $sql = "SELECT seller_id FROM seller_info WHERE seller_email = ?";
            $prepare_query = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($prepare_query, "s", $_SESSION['acct_email']);
            mysqli_stmt_execute($prepare_query);
            $result = mysqli_stmt_get_result($prepare_query);

            if ($row = mysqli_fetch_assoc($result)) {
                $seller_id = $row['seller_id'];

                $insert_sql = "INSERT INTO products (product_name, product_desc, product_price, product_quantity, product_pic, seller_id) 
                               VALUES (?, ?, ?, ?, ?, ?)";
                $insert_query = mysqli_prepare($conn, $insert_sql);
                mysqli_stmt_bind_param($insert_query, "ssdisi", $product_name, $product_desc, $product_price, $product_quantity, $target_file, $seller_id);
                
                if (mysqli_stmt_execute($insert_query)) {
                    $_SESSION['products'][] = array(
                        'name' => $product_name,
                        'desc' => $product_desc,
                        'price' => $product_price,
                        'quantity' => $product_quantity,
                        'pic' => $product_pic
                    );
                    header("Location: add_product_result.php");
                    exit();
                } else {
                    echo "Error executing query: " . mysqli_error($conn);
                }
            } else {
                echo "Seller not found.";
            }
        } else {
            echo "Failed to upload file.";
        }
    }
}
?>
