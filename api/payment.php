<?php
require('connect.php');  // ---------gerekli veri tabanı bağlantısını yapın !-----------------

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
date_default_timezone_set('Europe/Istanbul'); // Türkiye saat dilimine ayarla

function sendResponse($status, $message)
{
    http_response_code($status);
    echo json_encode(["message" => $message]);
    exit();
}


function GenerateSecurityData($terminalId,$provision_password)
{

    $data = [
        $provision_password,
        str_pad((int)$terminalId, 9, 0, STR_PAD_LEFT)
    ];

    $shaData = sha1(implode('', $data));

    return strtoupper($shaData);
}

function generateHashData($orderId, $terminalId, $cardNumber = null, $amount, $currencyCode,$provision_password)
{
    $hashedPassword = GenerateSecurityData($terminalId,$provision_password);

    $data = [
        $orderId,
        $terminalId,
        $cardNumber,
        $amount,
        $currencyCode,
        $hashedPassword
    ];

    $shaData = strtoupper(hash("sha512", implode('', $data)));

    return strtoupper($shaData);
}



function get2dSecure($conn, $order_id, $card_number, $expiry_date, $cvc, $name)
{
    $order_id_yeni = random_int(1000000000, 9999999999);
   $totalPrice = 0;
$sql_amount = "
    SELECT ol.totalPrice, ol.email, ol.ip, 
           ps.bank_name, ps.merchant_id, ps.prov_user_id, 
           ps.provision_password, ps.terminal_id, ps.store_key, 
           ps.secureType, ps.active
    FROM order_list ol
    INNER JOIN pos_settings ps ON ps.id = ol.pos_id
    WHERE ol.id = ? AND ps.active = 1";

$stmt = $conn->prepare($sql_amount);
$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalPrice = $row['totalPrice'];
    $email = $row['email'];
    $ip = $row['ip'];
    $bank_name = $row['bank_name'];
    $merchant_id = $row['merchant_id'];
    $prov_user_id = $row['prov_user_id'];
    $provision_password = $row['provision_password'];
    $terminal_id = $row['terminal_id'];
    $store_key = $row['store_key'];
    $secureType = $row['secureType'];

} else {
    sendResponse(404, 'Order or active POS not found');
}


    if (!$stmt->execute()) {
        sendResponse(500, 'Failed to update order status');
    }

    $currencyCode = 949; // Türk Lirası

    $hash_data = generateHashData($order_id_yeni, $terminal_id, $card_number, $totalPrice, $currencyCode,$provision_password);

    // JSON yanıtı
    $response = [
        'status' => 'success',
        'data' => [
            'hash_data' => $hash_data,
            'terminalId' => $terminal_id,
            'email' => $email,
            'card_number' => intval($card_number),
            'expiry_date' => $expiry_date,
            'cvc' => intval($cvc),
            'order_id' => intval($order_id),
            'totalPrice' => $totalPrice,
            'currencyCode' => $currencyCode,
            'securityType' => '2d',
            'order_id_yeni' => $order_id_yeni
        ]
    ];

    echo json_encode($response);
    exit();
}

function GenerateHashData3D($orderId,  $terminalId, $amount, $currencyCode, $store_key,$provision_password)
{
    $installmentCount = 0; // taksit sayısı
    $successUrl = "payment-successful.php";
    $errorUrl = "error.php";
    $type = "";
    $hashedPassword = GenerateSecurityData($terminalId,$provision_password);
    return strtoupper(hash('sha512', $terminalId . $orderId . $amount . $currencyCode . $successUrl . $errorUrl . $type . $installmentCount . $store_key . $hashedPassword));
}

function get3dSecure($conn, $order_id, $card_number, $expiry_date, $cvc, $name)
{
    $order_id_yeni = random_int(1000000000, 9999999999);
   $totalPrice = 0;
$sql_amount = "
    SELECT ol.totalPrice, ol.email, ol.ip, 
           ps.bank_name, ps.merchant_id, ps.prov_user_id, 
           ps.provision_password, ps.terminal_id, ps.store_key, 
           ps.secureType, ps.active
    FROM order_list ol
    INNER JOIN pos_settings ps ON ps.id = ol.pos_id
    WHERE ol.id = ? AND ps.active = 1";

$stmt = $conn->prepare($sql_amount);
$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $totalPrice = $row['totalPrice'];
    $email = $row['email'];
    $ip = $row['ip'];
    $bank_name = $row['bank_name'];
    $merchant_id = $row['merchant_id'];
    $prov_user_id = $row['prov_user_id'];
    $provision_password = $row['provision_password'];
    $terminal_id = $row['terminal_id'];
    $store_key = $row['store_key'];
    $secureType = $row['secureType'];

} else {
    sendResponse(404, 'Order or active POS not found');
}

    $currencyCode = 949; // Türk Lirası

    $hash_data = GenerateHashData3D($order_id_yeni, $terminal_id, $totalPrice, $currencyCode,$store_key,$provision_password);

    $postData = [
        'mode' => 'TEST',
        'apiversion' => '512',
        'secure3dsecuritylevel' => '3D_PAY',
        'terminalprovuserid' => $prov_user_id,
        'terminaluserid' => 'GARANTI',
        'terminalmerchantid' => $merchant_id,
        'terminalid' => $terminal_id,
        'orderid' => $order_id_yeni,
        'successurl' => 'payment-successful.php',
        'errorurl' => 'payment-failed.php',
        'customeremailaddress' => $email,
        'customeripaddress' => $_SERVER['REMOTE_ADDR'],
        'companyname' => 'GARANTI TEST',
        'lang' => 'tr',
        'txntimestamp' => gmdate('Y-m-d\TH:i:s\Z'),
        'refreshtime' => '1',
        'secure3dhash' => $hash_data, 
        'txnamount' => $totalPrice,
        'txntype' => 'sales',
        'txncurrencycode' => $currencyCode,
        'txninstallmentcount' => '',
        'cardholdername' => 'Test User',
        'cardnumber' => $card_number,
        'cardexpiredatemonth' => substr($expiry_date, 0, 2),
        'cardexpiredateyear' => substr($expiry_date, 2, 2),
        'cardcvv2' => $cvc,
    ];
    
$html = '<form method="post" role="form" action="https://sanalposprovtest.garantibbva.com.tr/servlet/gt3dengine">
    <input type="hidden" name="mode" id="mode" value="' . $postData['mode'] . '" />
    <input type="hidden" name="apiversion" id="apiversion" value="' . $postData['apiversion'] . '" />
    <input type="hidden" name="secure3dsecuritylevel" id="secure3dsecuritylevel" value="' . $postData['secure3dsecuritylevel'] . '" />
    <input type="hidden" name="terminalprovuserid" id="terminalprovuserid" value="' . $postData['terminalprovuserid'] . '" />
    <input type="hidden" name="terminaluserid" id="terminaluserid" value="' . $postData['terminaluserid'] . '" />
    <input type="hidden" name="terminalmerchantid" id="terminalmerchantid" value="' . $postData['terminalmerchantid'] . '" />
    <input type="hidden" name="terminalid" id="terminalid" value="' . $postData['terminalid'] . '" />
    <input type="hidden" name="orderid" id="orderid" value="' . $postData['orderid'] . '" />
    <input type="hidden" name="successurl" id="successurl" value="' . $postData['successurl'] . '" />
    <input type="hidden" name="errorurl" id="errorurl" value="' . $postData['errorurl'] . '" />
    <input type="hidden" name="customeremailaddress" id="customeremailaddress" value="' . $postData['customeremailaddress'] . '" />
    <input type="hidden" name="customeripaddress" id="customeripaddress" value="' . $postData['customeripaddress'] . '" />
    <input type="hidden" name="companyname" id="companyname" value="' . $postData['companyname'] . '" />
    <input type="hidden" name="lang" id="lang" value="' . $postData['lang'] . '" />
    <input type="hidden" name="txntimestamp" id="txntimestamp" value="' . $postData['txntimestamp'] . '" />
    <input type="hidden" name="refreshtime" id="refreshtime" value="' . $postData['refreshtime'] . '" />
    <input type="hidden" name="secure3dhash" id="secure3dhash" value="' . $postData['secure3dhash'] . '" />
    <input type="hidden" name="txnamount" id="txnamount" value="' . $postData['txnamount'] . '" />
    <input type="hidden" name="txntype" id="txntype" value="' . $postData['txntype'] . '" />
    <input type="hidden" name="txncurrencycode" id="txncurrencycode" value="' . $postData['txncurrencycode'] . '" />
    <input type="hidden" name="txninstallmentcount" id="txninstallmentcount" value="' . $postData['txninstallmentcount'] . '" />
    <input name="cardholdername" value="' . $postData['cardholdername'] . '" />
    <input name="cardnumber" value="' . $postData['cardnumber'] . '" />
    <input name="cardexpiredatemonth" value="' . $postData['cardexpiredatemonth'] . '" />
    <input name="cardexpiredateyear" value="' . $postData['cardexpiredateyear'] . '" />
    <input name="cardcvv2" value="' . $postData['cardcvv2'] . '" />
</form>';



    // JSON yanıtı
    $response = [
        'status' => 'success',
        'data' => [
            'hash_data' => $hash_data,
            'terminalId' => $$terminal_id,
            'email' => $email,
            'card_number' => intval($card_number),
            'expiry_date' => $expiry_date,
            'cvc' => intval($cvc),
            'order_id' => intval($order_id),
            'totalPrice' => $totalPrice,
            'currencyCode' => $currencyCode,
            'securityType' => '3d',
            'order_id_yeni' => $order_id_yeni
        ],
        'html' => $html
        
    ];
    
    
    

    echo json_encode($response);
    exit();
}

// İstek türünü ve URL'yi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST verilerini filtreleyin
    $is_2d_secure = filter_var($_POST['2dsecure'], FILTER_VALIDATE_BOOLEAN);
    $is_3d_secure = filter_var($_POST['3dsecure'], FILTER_VALIDATE_BOOLEAN);
    $card_number = filter_input(INPUT_POST, 'card-number', FILTER_SANITIZE_STRING);
    $expiry_month = filter_input(INPUT_POST, 'expiry-month', FILTER_SANITIZE_STRING);
    $expiry_year = filter_input(INPUT_POST, 'expiry-year', FILTER_SANITIZE_STRING);
    $cvc = filter_input(INPUT_POST, 'cvc', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $order_id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $expiry_date = $expiry_month . $expiry_year;
    if ($is_2d_secure) {

        get2dSecure($conn, $order_id, $card_number, $expiry_date, $cvc, $name);
    } elseif ($is_3d_secure) {

        get3dSecure($conn, $order_id, $card_number, $expiry_date, $cvc, $name);
    } else {
        sendResponse(400, 'Invalid request');
    }
} else {
    sendResponse(405, 'Method Not Allowed');
}


$conn->close();
