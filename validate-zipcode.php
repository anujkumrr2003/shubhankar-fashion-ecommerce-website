<?php


 

header("Content-Type: application/json");

/*
|--------------------------------------------------------------------------
| Get Data
|--------------------------------------------------------------------------
*/

$country = strtoupper(trim($_POST['country'] ?? ''));
$zipcode = strtoupper(trim($_POST['zipcode'] ?? ''));



if ($country == "" || $zipcode == "") {
    echo json_encode([
        "status" => false,
        "message" => "Country and ZIP Code are required."
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| INDIA
|--------------------------------------------------------------------------
*/

if ($country == "IN") {

    if (preg_match('/^[1-9][0-9]{5}$/', $zipcode)) {

        echo json_encode([
            "status" => true
        ]);

    } else {

        echo json_encode([
            "status" => false,
            "message" => "Invalid Indian PIN Code."
        ]);

    }

    exit;
}

/*
|--------------------------------------------------------------------------
| USA / CANADA / GERMANY
|--------------------------------------------------------------------------
*/

if (in_array($country, ["US","CA","DE"])) {

    $url = "https://api.zippopotam.us/" .
            strtolower($country) .
            "/" .
            urlencode($zipcode);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode == 200) {

        echo json_encode([
            "status" => true
        ]);

    } else {

        echo json_encode([
            "status" => false,
            "message" => "Invalid ZIP Code."
        ]);

    }

    exit;
}

/*
|--------------------------------------------------------------------------
| UNITED KINGDOM
|--------------------------------------------------------------------------
*/

if ($country == "GB") {

    $url = "https://api.postcodes.io/postcodes/" .
            urlencode($zipcode) .
            "/validate";

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);

    curl_close($ch);

    $json = json_decode($response, true);

    if (!empty($json['result']) && $json['result'] === true) {

        echo json_encode([
            "status" => true
        ]);

    } else {

        echo json_encode([
            "status" => false,
            "message" => "Invalid UK Postcode."
        ]);

    }

    exit;
}

/*
|--------------------------------------------------------------------------
| AUSTRALIA
|--------------------------------------------------------------------------
*/

if ($country == "AU") {

    if (preg_match('/^[0-9]{4}$/', $zipcode)) {

        echo json_encode([
            "status" => true
        ]);

    } else {

        echo json_encode([
            "status" => false,
            "message" => "Invalid Australian Postcode."
        ]);

    }

    exit;
}

/*
|--------------------------------------------------------------------------
| UAE
|--------------------------------------------------------------------------
*/

if ($country == "AE") {

    if (preg_match('/^[0-9]{5}$/', $zipcode)) {

        echo json_encode([
            "status" => true
        ]);

    } else {

        echo json_encode([
            "status" => false,
            "message" => "Invalid UAE ZIP Code."
        ]);

    }

    exit;
}

/*
|--------------------------------------------------------------------------
| SINGAPORE
|--------------------------------------------------------------------------
*/

if ($country == "SG") {

    if (preg_match('/^[0-9]{6}$/', $zipcode)) {

        echo json_encode([
            "status" => true
        ]);

    } else {

        echo json_encode([
            "status" => false,
            "message" => "Invalid Singapore Postal Code."
        ]);

    }

    exit;
}

/*
|--------------------------------------------------------------------------
| Default
|--------------------------------------------------------------------------
*/

echo json_encode([
    "status" => false,
    "message" => "ZIP Code validation is not available for this country."
]);

exit;
?>


