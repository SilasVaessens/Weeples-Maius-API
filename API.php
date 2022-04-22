<?php
  error_reporting(0);
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

 //onderstaand command is vereist voor het kunnen testen met Postman:
 //php -S localhost:80
 //code voor debuggen terminal:
 //error_log('wat ik wil debuggen', 3, "php://stdout");


  $data = json_decode(file_get_contents('php://input'), true);
  http_response_code(400);

  if(empty($data['description'])){
    echo json_encode(array('message' => 'Beschrijving van de activiteit vereist voor het aanmaken van een activiteit op Weeples'));
  }
  elseif(empty($data['datetime'])){
    echo json_encode(array('message' => 'Unix-tijd van wanneer de activiteit plaatsvindt vereist voor het aanmaken van een activiteit op Weeples'));
  }
  elseif(empty($data['location'])){
    echo json_encode(array('message' => 'Locatiegegevens bestaande uit een naam en coördinaten of de geohash van de locatie van de activiteit zijn vereist voor het aanmaken van een activiteit op Weeples'));
  }
  elseif(empty($data['location']['geohash']) && (empty($data['location']['coordinates']['latitude']) || empty($data['location']['coordinates']['longitude']))){
    echo json_encode(array('message' => 'Coördinaten of de geohash van de locatie van de activiteit zijn vereist voor het aanmaken van een activiteit op Weeples'));
  }
  elseif(empty($data['location']['name'])){
    echo json_encode(array('message' => 'Naam van de locatie van de activiteit vereist voor het aanmaken van een activiteit op Weeples'));
  }
  elseif(empty($data['name'])){
    echo json_encode(array('message' => 'Naam van de activiteit vereist voor het aanmaken van een activiteit op Weeples'));
  }

  else {
    $data = json_encode($data);

    $url = "https://jsonplaceholder.typicode.com/posts";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);


    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    $resp = curl_exec($curl);
    $info = curl_getinfo($curl);

    if ($info['http_code'] > 199 && $info['http_code'] < 300){
      http_response_code($info['http_code']);
      echo json_encode(array('message' => 'Activieit op Weeples aangemaakt', 'data' => json_decode($resp)));
    }
    else {
      http_response_code(500);
      echo json_encode(array('message' => 'Iets ging mis bij Weeples'));
    }
    curl_close($curl);
  }  
?>