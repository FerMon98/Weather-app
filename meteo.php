<?php

error_reporting(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);


$year = date("Y");

//ciudad por defecto
$ciudad = "Barcelona";


if ($_GET) {
    $ciudad = urlencode($_GET['city']);  // Encode the city name
}

$API_KEY = "2773a9b09a08c0d8fb33c6a7b7edbae0";

$URL = "https://api.openweathermap.org/data/2.5/weather?"; //hacerlo asi construye el URL y es mas legible
$URL.= "q=$ciudad";
$URL.= "&appid=$API_KEY"; //el . ayuda a empalmar el link.
$URL.= "&units=metric";
$URL.= "&lang=en";

//echo $URL;

$file = file_get_contents($URL);
//echo ($file);

$json_meteo = json_decode($file, true);
//print_r($json_meteo); Hay que utilizar print para objetos json

// Fetch the weather data
$icono = $json_meteo['weather'][0]['icon'];
$description = $json_meteo['weather'][0]['description'];
$temp = $json_meteo['main']['temp'];
$min_temp = $json_meteo['main']['temp_min'];
$max_temp = $json_meteo['main']['temp_max'];
$humidity = $json_meteo['main']['humidity'];


// Fetch the timezone offset from the JSON response (in seconds)
$timezone_offset = $json_meteo['timezone']; // Timezone offset from UTC (in seconds)

// Get the sunrise and sunset times (both are in UNIX timestamp format)
$sunrise_time = $json_meteo['sys']['sunrise'];
$sunset_time = $json_meteo['sys']['sunset'];

// Create DateTime objects for sunrise and sunset, using UTC as the base timezone
$sunrise = new DateTime('@' . ($sunrise_time + $timezone_offset));  // Adjust with timezone offset
$sunset = new DateTime('@' . ($sunset_time + $timezone_offset));    // Adjust with timezone offset

// Format the sunrise and sunset times to a readable format (e.g., H:i)
$sunrise_formatted = $sunrise->format('H:i');
$sunset_formatted = $sunset->format('H:i');



/* 5 DAY FORECAST CODE */

$URLF = "https://api.openweathermap.org/data/2.5/forecast?q=$ciudad&appid=$API_KEY&units=metric&lang=en";
$filef = file_get_contents($URLF);
$json_forecast = json_decode($filef, true);

//echo $URLF;

// Loop through the forecast list (every 3 hours) as all the info is saved in the list array
$forecast_list = $json_forecast['list'];

// Array to store daily forecast
$daily_forecast = [];


// Group forecast by day

foreach ($forecast_list as $forecast) {
    //Convert the timestamp to a readable date
    $date = date('Y-m-d', $forecast['dt']);
    $day = date('l', $forecast['dt']); // Get the name of the day of the week

    //Extract data
    $icon = $forecast['weather'][0]['icon'];
    $description = $forecast['weather'][0]['description'];
    $temp = $forecast['main']['temp'];
    $humidity = $forecast['main']['humidity'];

    //Group forecast data by date
    if (!isset($daily_forecast[$date])) {

        $daily_forecast[$date] = [
            'date' => $date, // Add the date here
            'day' => $day,
            'icon' => $icon,
            'description' => $description,
            'temp_min' => $temp,
            'temp_max' => $temp,
            'humidity' => $humidity // optional: you can store humidity if needed
        ];
    } 
    
    else {
        // Update min/max temps for the day
        $daily_forecast[$date]['temp_min'] = min($daily_forecast[$date]['temp_min'], $temp);
        $daily_forecast[$date]['temp_max'] = max($daily_forecast[$date]['temp_max'], $temp);
    }
    
}

// Convert the associative array to a simple indexed array for easier iteration in the HTML
$daily_forecast = array_values($daily_forecast);

//echo $daily_forecast

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta author="Fernanda Montalvan">
    <title>Weather app</title>
    <link rel="stylesheet" href="resources/css/style.css">
    <link rel="shortcut icon" href="resources/img/thermometer-temperature-svgrepo-com.svg" type="image/x-icon">
</head>
<body>
    <header>
        <div>
          <h1>Welcome to My Weather App</h1> 
          <img src="resources/img/01d.svg" alt="" class="sol">  
        </div>
        
        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">About</a></li>
                <li><a href="#">Contact</a></li>
            </ul>

            <div id="weather-icons"></div>

            <div id="weather-location"></div>
        </nav>
    </header>

    <main>
        <section>
            <div id="search">
                <form method="get">
                    <h2>Search for a city</h2>
                    <div id="city-search">
                        <input type="text" id="city" placeholder="Enter a city name" name="city">
                        <button type="submit" onclick="searchWeather()" title="search"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search-heart" viewBox="0 0 16 16">
                            <path d="M6.5 4.482c1.664-1.673 5.825 1.254 0 5.018-5.825-3.764-1.664-6.69 0-5.018"/>
                            <path d="M13 6.5a6.47 6.47 0 0 1-1.258 3.844q.06.044.115.098l3.85 3.85a1 1 0 0 1-1.414 1.415l-3.85-3.85a1 1 0 0 1-.1-.115h.002A6.5 6.5 0 1 1 13 6.5M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11"/>
                          </svg></button>
                    </div>
                </form>
            </div>
        </section>

        <section>
            <div class="cityWeather">
            <p id="nombre_ciudad"><strong><?php echo urldecode($ciudad); ?></strong></p>
                <div id="current-weather">
                    <p class="temp_actual">Current Temperature: <span><?php echo $temp?>°C</span></p>
                    <img src="resources/img/<?php echo $icono;?>.svg" alt="<?php echo $description;?>">
                </div>
                
                <div class="details">
                    <p class="temp_min">Min Temp: <span><?php echo $min_temp?>°C</span></p>
                    <p class="temp_max">Max Temp: <span><?php echo $max_temp?>°C</span></p>
                    <p class="humedad"> Humidity: <span><?php echo $humidity?>%</span></p>
                    <p class="amanecer"> <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-sunrise-fill" viewBox="0 0 16 16">
                        <path d="M7.646 1.146a.5.5 0 0 1 .708 0l1.5 1.5a.5.5 0 0 1-.708.708L8.5 2.707V4.5a.5.5 0 0 1-1 0V2.707l-.646.647a.5.5 0 1 1-.708-.708zM2.343 4.343a.5.5 0 0 1 .707 0l1.414 1.414a.5.5 0 0 1-.707.707L2.343 5.05a.5.5 0 0 1 0-.707m11.314 0a.5.5 0 0 1 0 .707l-1.414 1.414a.5.5 0 1 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0M11.709 11.5a4 4 0 1 0-7.418 0H.5a.5.5 0 0 0 0 1h15a.5.5 0 0 0 0-1h-3.79zM0 10a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2A.5.5 0 0 1 0 10m13 0a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/></svg> <span><?php echo $sunrise_formatted; ?></span>
                    </p>

                    <p class="puesta"> <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi bi-sunset-fill" viewBox="0 0 16 16">
                        <path d="M7.646 4.854a.5.5 0 0 0 .708 0l1.5-1.5a.5.5 0 0 0-.708-.708l-.646.647V1.5a.5.5 0 0 0-1 0v1.793l-.646-.647a.5.5 0 1 0-.708.708zm-5.303-.51a.5.5 0 0 1 .707 0l1.414 1.413a.5.5 0 0 1-.707.707L2.343 5.05a.5.5 0 0 1 0-.707zm11.314 0a.5.5 0 0 1 0 .706l-1.414 1.414a.5.5 0 1 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zM11.709 11.5a4 4 0 1 0-7.418 0H.5a.5.5 0 0 0 0 1h15a.5.5 0 0 0 0-1h-3.79zM0 10a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2A.5.5 0 0 1 0 10m13 0a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5"/> </svg> <span><?php echo $sunset_formatted; ?></span>
                    </p>

                </div>
            </div>
        </section>
        
        <section>
            <div id="forecast">
                <h3>5-Day Forecast</h3>
                <div id="daily-forecast">
                    <?php foreach ($daily_forecast as $data): ?>
                    <div class="forecast-day">
                        <h4><?php echo $data['day']; ?> (<?php echo date('Y-m-d', strtotime($data['date'])); ?>)</h4> <br>
                        <p><strong>Temperature: <br></strong> <?php echo $data['temp_min']; ?> - <?php echo $data['temp_max']; ?>°C</p>
                        <p><strong>Expected Weather:</strong> <br><?php echo $data['description']; ?></p><br>
                        <img src="http://openweathermap.org/img/wn/<?php echo $data['icon']; ?>.png" alt="<?php echo $data['description']; ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section>
            <div id="alerts">
                <h3>Weather Alerts</h3>
                <div id="weather-alerts"></div>  
            </div>
        </section>        
    </main>

    <footer>
        <p>&copy; (<?php echo($year);?>) MyWeatherApp. All rights reserved.</p>
    </footer>
</body>
</html>