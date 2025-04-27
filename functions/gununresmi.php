<?php
function GununResmi() {

  $ch = curl_init();
  $date = date("Y-m-d", rand(strtotime("1995-06-16"), strtotime("now")));
  curl_setopt($ch, CURLOPT_URL, "https://api.nasa.gov/planetary/apod?date=$date&api_key=regQvbRL2BCAbxpuiffT88Vf7GbBEWaQWEE6cUXT");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $response = json_decode(curl_exec($ch));
  curl_close($ch);
  $url = $response->url;
  $title = $response->title;
  $explanation = $response->explanation;
  echo "<h2>$title</h2>";
  echo "<img class='img-fluid' src='$url' alt='$title' width='120' height='120'>";
  // echo "<p>$explanation</p>";
}

?>