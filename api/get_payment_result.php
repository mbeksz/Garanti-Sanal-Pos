<?php
include 'connect.php';  // -----------veri tabanı bağlantınızı yapın -------------------
include 'settings.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getTotalCost($array = [])
{
    global $conn;

    $totalCost = 0;

    if (!empty($array)) {
        foreach ($array as $item) {
            $productId = $item['id'];
            $productQuantity = $item['quantity'];
            $attributes = $item['variations'];
            $attributeNames = array_values($attributes);

            $attributeIds = [];
            $query = "SELECT id, name FROM attributes WHERE name IN ('" . implode("','", $attributeNames) . "')";
            $result = mysqli_query($conn, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $attributeIds[$row['name']] = $row['id'];
                }
            }

            $variationTotalCost = 0;

            foreach ($attributeIds as $attrId) {
                $query = "SELECT * FROM products WHERE attribute_id = $attrId AND parent_product = $productId";
                $result = mysqli_query($conn, $query);

                if ($result) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $unitPrice = $row['unit_price'];
                        $discount = $row['discount'];

                        $discountedPrice = $unitPrice * (1 - $discount / 100);

                        $totalPrice = $discountedPrice * $productQuantity;
                        $variationTotalCost += $totalPrice;
                    }
                } else {
                    echo 'Error in products query: ' . mysqli_error($conn);
                }
            }

            $query = "SELECT unit_price, discount FROM products WHERE id = $productId";
            $result = mysqli_query($conn, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $baseUnitPrice = $row['unit_price'];
                    $baseDiscount = $row['discount'];
                    $baseDiscountedPrice = $baseUnitPrice * (1 - $baseDiscount / 100);
                    $baseTotalPrice = $baseDiscountedPrice * $productQuantity;
                    $totalCost += (int)($baseTotalPrice + $variationTotalCost);
                    $totalCost2 = number_format($totalCost, 0, ',', '.');
                }
            } else {
                echo 'Error in base product query: ' . mysqli_error($conn);
            }
        }
    }
    $GLOBALS['totalCost'] = $totalCost;
    return $totalCost;
}


if (isset($_POST['country'])) {
    $country = $conn->real_escape_string($_POST['country']);
    $sqlc = "SELECT price, delivery_time FROM cargo WHERE country = '$country'";
    $result = $conn->query($sqlc);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $formattedPrice = $row['price'];

        $shipping_info = array(
            'price' => $formattedPrice,
            'delivery_time' => $row['delivery_time']
        );
        echo json_encode($shipping_info);
    } else {
        echo json_encode(array("error" => "Kargo bilgisi bulunamadı"));
    }
}

global $ip;

$basketItems = [];
if (isset($_COOKIE['basket'])) {
    $basketValue = $_COOKIE['basket'];
    $basketItems = json_decode($basketValue, true);
}

if (isset($_POST['save_settings'])) {
    session_start();
    $userId = $_SESSION['id'];
    $userId = intval($userId);
    echo "user id ";
    echo  $userId;
    $country = $_POST['country_name'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $vergi_no = $_POST['vergi_no'];
    $vergi_dairesi = $_POST['vergi_dairesi'];
    $name_bill = $_POST['name_bill'];
    $no_bill = $_POST['no_bill'];
    $email_bill = $_POST['email_bill'];
    $address_bill = $_POST['address_bill'];
    $country_bill = $_POST['country_bill'];
    $city_bill = $_POST['city_bill'];
    $delivery = $_POST['delivery'];
    $bill_type = $_POST['bill_type'];
    $ip = $_POST['ip'];
    $unique_key = uniqid();


    $sqlcountry = "SELECT * FROM cargo WHERE country = ?";
    $stmt = $conn->prepare($sqlcountry);
    $stmt->bind_param("s", $country);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $cargoPrice = $row['price'];
    }


    $last_grand_total = getTotalCost($basketItems) + floatval($cargoPrice);
    $product_total = getTotalCost($basketItems);
    $product_total = (int)$product_total;
    $last_grand_total = (int)$last_grand_total;

    $sql = "INSERT INTO order_list (
        name, phone, email, address, country, city, vergi_no, vergi_dairesi,
        name_bill, no_bill, email_bill, address_bill, country_bill, city_bill, delivery, bill_type, productTotalCost , totalPrice, cargoPrice, ip, user_id , unique_key
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,? ,? ,?,?,?, ?
    )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssssssssssiisis",
        $name,
        $phone,
        $email,
        $address,
        $country,
        $city,
        $vergi_no,
        $vergi_dairesi,
        $name_bill,
        $no_bill,
        $email_bill,
        $address_bill,
        $country_bill,
        $city_bill,
        $delivery,
        $bill_type,
        $product_total,
        $last_grand_total,
        $cargoPrice,
        $ip,
        $userId,
        $unique_key
    );

    if ($stmt->execute()) {
        echo "Kayıt başarıyla eklendi.";
    } else {
        echo "Hata: " . $stmt->error;
    }

    $stmt = $conn->prepare("SELECT * FROM order_list WHERE ip = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("s", $ip);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $order_id = $row['id'];
        echo "ID: " . $order_id . "<br>";

        $sql_add = "INSERT INTO order_items (order_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt2 = $conn->prepare($sql_add);

        foreach ($basketItems as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];
            $stmt2->bind_param("sss", $order_id, $product_id, $quantity);
            $stmt2->execute();

            $order_item_id = $stmt2->insert_id;

            if (!empty($item['variations'])) {
                foreach ($item['variations'] as $variationName => $attributeName) {
                    $sql_variation = "SELECT id FROM variations WHERE name = ?";
                    $stmt_var = $conn->prepare($sql_variation);
                    $stmt_var->bind_param("s", $variationName);
                    $stmt_var->execute();
                    $result_var = $stmt_var->get_result();
                    if ($result_var->num_rows > 0) {
                        $variation_row = $result_var->fetch_assoc();
                        $variation_id = $variation_row['id'];
                    }
                    $stmt_var->close();

                    $sql_attribute = "SELECT id FROM attributes WHERE name = ?";
                    $stmt_attr = $conn->prepare($sql_attribute);
                    $stmt_attr->bind_param("s", $attributeName);
                    $stmt_attr->execute();
                    $result_attr = $stmt_attr->get_result();
                    if ($result_attr->num_rows > 0) {
                        $attribute_row = $result_attr->fetch_assoc();
                        $attribute_id = $attribute_row['id'];
                    }
                    $stmt_attr->close();

                    $sql_order_variations = "INSERT INTO order_variations (product_id, order_id, order_item_id, variation_id, attribute_id) VALUES (?, ?, ?, ?, ?)";
                    $stmt_order_var = $conn->prepare($sql_order_variations);
                    $stmt_order_var->bind_param("iiiii", $product_id, $order_id, $order_item_id, $variation_id, $attribute_id);
                    $stmt_order_var->execute();
                    $stmt_order_var->close();
                }
            }
        }

        $stmt2->close();
    } else {
        echo "Varyasyonlarda hata";
    }

    $stmt->close();
    $conn->close();
}
if (isset($_POST['securityType'])) {

    if ($_POST['securityType'] == '2d') {
        header('Content-Type: application/json');

        $data = $_POST['data'];
        $card_number = $data['card_number'];
        $currencyCode = $data['currencyCode'];
        $expiry_date = $data['expiry_date'];
        $email = $data['email'];
        $hash_data = $data['hash_data'];
        $cvc = $data['cvc'];
        $order_id = $data['order_id'];
        $terminalId = $data['terminalId'];
        $totalPrice = $data['totalPrice'];
        $order_id_yeni = $data['order_id_yeni'];

        $xml = '<?xml version="1.0" encoding="iso-8859-9"?>
                <GVPSRequest>
                    <Mode>TEST</Mode>
                    <Version>512</Version>
                    <Terminal>
                        <ProvUserID>PROVAUT</ProvUserID>
                        <HashData>' . htmlspecialchars($hash_data, ENT_XML1, 'UTF-8') . '</HashData>
                        <UserID>PROVAUT</UserID>
                        <ID>' . htmlspecialchars($terminalId, ENT_XML1, 'UTF-8') . '</ID>
                        <MerchantID>7000679</MerchantID>
                    </Terminal>
                    <Customer>
                        <IPAddress>192.168.0.1</IPAddress>
                        <EmailAddress>' . htmlspecialchars($email, ENT_XML1, 'UTF-8') . '</EmailAddress>
                    </Customer>
                    <Card>
                        <Number>' . htmlspecialchars($card_number, ENT_XML1, 'UTF-8') . '</Number>
                        <ExpireDate>' . htmlspecialchars($expiry_date, ENT_XML1, 'UTF-8') . '</ExpireDate>
                        <CVV2>' . htmlspecialchars($cvc, ENT_XML1, 'UTF-8') . '</CVV2>
                    </Card>
                    <Order>
                        <OrderID>' . htmlspecialchars($order_id_yeni, ENT_XML1, 'UTF-8') . '</OrderID>
                        <GroupID />
                    </Order>
                    <Transaction>
                        <Type>sales</Type>
                        <Amount>' . htmlspecialchars($totalPrice, ENT_XML1, 'UTF-8') . '</Amount>
                        <CurrencyCode>' . htmlspecialchars($currencyCode, ENT_XML1, 'UTF-8') . '</CurrencyCode>
                        <CardholderPresentCode>0</CardholderPresentCode>
                        <MotoInd>N</MotoInd>
                    </Transaction>
                </GVPSRequest>';

        $api_url = 'https://sanalposprovtest.garantibbva.com.tr/VPServlet';

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/xml',
            'Content-Length: ' . strlen($xml)
        ));

        $response = curl_exec($ch);

        if ($response === false) {
            echo json_encode(['status' => 'failed', 'message' => 'cURL Error: ' . curl_error($ch)]);
        } else {


            $xml_response = simplexml_load_string($response);

            if ($xml_response !== false) {
                $code = (string)$xml_response->Transaction->Response->Code;
                $message = (string)$xml_response->Transaction->Response->Message;
                $ErrorMsg = (string)$xml_response->Transaction->Response->ErrorMsg;
                $SysErrMsg = (string)$xml_response->Transaction->Response->SysErrMsg;

                if ($code === '00' && $message === 'Approved') {
                    echo json_encode(['status' => 'success']);
                } else {
                    echo json_encode(['status' => 'failed', 'message' => " Kod: $code, Mesaj: $message, Hata nedeni: $ErrorMsg, Sistem hata mesajı: $SysErrMsg"]);
                }
            } else {
                echo json_encode(['status' => 'failed', 'message' => 'XML yanıtı işlenemedi.']);
            }
        }

        curl_close($ch);
    } else if ($_POST['securityType'] == '3d') {
        header('Content-Type: application/json');

        // api/payment.php sayfasından gönderim yapıyoruz formu burada sadece ödeme başarılı diye veri tabanında güncelleme yapılacak


    }
}
