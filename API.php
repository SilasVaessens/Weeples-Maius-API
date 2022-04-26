<?php
  error_reporting(0); //zorgt ervoor dat er geen warnings verstuurt worden als er iets misgaat of ontbreekt bij $data     
                      //als bijvoorbeeld de 'datetime' mist, wordt er ook een waarschuwing meegestuurd dat de het veld 'datetime' null is

  //set headers voor het ontvangen van http requests
  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: POST");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

 //onderstaand command is vereist voor het kunnen testen met Postman:
 //php -S localhost:80
 //code voor debuggen terminal:
 //error_log('wat ik wil debuggen', 3, "php://stdout");


  $data = json_decode(file_get_contents('php://input'), true); //haalt de body op van de json post, decode het en zet het om in een php object
  http_response_code(400);//zet de http status naar 400, iedere echo die ontvangen wordt vanaf hier heeft 400 als http status


  //controleer of alle vereiste velden aanwezig/vol zijn, zo niet echo het eerste wat er ontbreekt naar de verstuurder van de post request
  //json_encode is nodig om er voor te zorgen dat het antwoord als een json file uitgelezen kan worden door de verstuurder
  //van de post request
  //ander idee is het verzamelen van de ontbrekende data in een array, en dan een echo terugsturen met alle ontbrekende data, als er ontbrekende data is
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
    //aangezien geohash of coördinaten vereist zijn, wordt gecontroleerd of beide, of onderdelen van beide missen
    //doet niks bijvoorbeeld als er geen geohash is, maar wel de volledige coördinaten
  }
  elseif(empty($data['location']['name'])){
    echo json_encode(array('message' => 'Naam van de locatie van de activiteit vereist voor het aanmaken van een activiteit op Weeples'));
  }
  elseif(empty($data['name'])){
    echo json_encode(array('message' => 'Naam van de activiteit vereist voor het aanmaken van een activiteit op Weeples'));
  }

  else { //alle vereiste data is aanwezig
    $data = json_encode($data);//maak weer een JSON object van de eerder uitgelezen data

    $url = "http://jsonplaceholder.typicode.com/posts"; //url waar een request naar toe gemaakt wordt, heb het werkend gekregen met http, maar niet met https


    //curl moet geinstalleerd zijn voor de werking van onderstaand gedeelte
    $curl = curl_init($url);//start een curl sessie, variabele is nodig voor het gebruik van de curl_setopt(), curl_exec(), en curl_close() functies
    curl_setopt($curl, CURLOPT_URL, $url); //niet per se nodig, set de url van de curl sessie, gebeurd ook bij curl_init 
    curl_setopt($curl, CURLOPT_POST, true); //maakt de http request een post request
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));//voegt een header toe, in dit geval dat de content type json is, maar andere headers zijn ook mogelijk
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //zorgt ervoor dat het antwoord als een string wordt gereturned
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); //set de body van het request, werkt alleen bij post request
    // voor meer curl_setopt opties, kijk op https://www.php.net/manual/en/function.curl-setopt.php


    $resp = curl_exec($curl); //voert het curl request uit, returnt true of false, met RETURNTRANSFER op true, returnt het resultaat ipv true 
    $info = curl_getinfo($curl); //haal meer info op van het laatst uitgevoerde curl request, zie https://www.php.net/manual/en/function.curl-getinfo.php
                                //voor alle mogelijke informatie

    if ($resp !== false){ //controleer of curl request gelukt is
      if ($info['http_code'] > 199 && $info['http_code'] < 300){ //controleer de http status van het curl request in de 200 is
        http_response_code($info['http_code']); //zet de http status van de echo
        echo json_encode(array('message' => 'Activieit op Weeples aangemaakt', 'activity' => json_decode($resp))); //echo bij code in de 200
      }
      else { // als deze else bereikt wordt, dan is waarschijnlijk iets goed mis gegaan. Voor betekenis van alle http status codes, zie https://www.restapitutorial.com/httpstatuscodes.html 
        http_response_code($info['http_code']);
        echo json_encode(array('message' => 'Iets ging mis bij Weeples of de API tussen Weeples en Maius'));// echo bij code niet in de 200
      }
    }
    else {// curl request mislukt, gebeurd ook als url https is ipv http
      http_response_code(500);
      echo json_encode(array('message' => 'Iets ging mis bij Weeples')); // echo
    }

    curl_close($curl);//beeindig de curl sessie
  }
?>
