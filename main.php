<?php
 // this is for establishing connection
$hostname = "localhost";
$username = "root";
$password = "";
$database = "we";

$conn = new mysqli($hostname, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$weather = array(); // Initialize $weather array

// this below code is for checking if th form is submitted using POST method or not
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["boxinput"])) {

        //this code is for retrieving  user input and openWeatherMap API methods
        $user_city = $_POST["boxinput"];
        $api_key = "2bcae1a123d7709d70bb7e9c3677dc0f";
        $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($user_city) . "&appid=" . $api_key . "&units=metric";
       // this line is for fetch weather data from OpenWeatherMap API
        $json_data = file_get_contents($url);

        if (!$json_data) {
            echo "City name is invalid";
        } else {
            // this line is for decode the json data
            $data = json_decode($json_data, true);

            //this line is for extract relevant weather infromation
            $date = date("Y-m-d");
            $temperature = $data['main']['temp'];
            $humidity = $data['main']['humidity'];
            $pressure = $data['main']['pressure'];
            $speed = $data['wind']['speed'];
            $city = $data['name'];

            // now this line is for insert the all data of weather into database
            $sql = "INSERT INTO datatable(date, cityName, Temperature, WindSpeed, Pressure, Humidity)
                    VALUES ('$date', '$city', '$temperature', '$speed', '$pressure', '$humidity')";

            if (mysqli_query($conn, $sql)) {
                // Success message or redirection can be added here
            } else {
                echo "Error: " . mysqli_error($conn);
            }
            //this line is for retrieve weather data form database for the specified city 
            $table = "SELECT * FROM datatable where cityName='$city'";
            $result = mysqli_query($conn, $table);

            //now this line is for check database queryis successful or not
            if (!$result) {
                echo "Error: " . mysqli_error($conn);
            }

            //now storing the retrieved weather data in the $weather Array
            while ($row = mysqli_fetch_assoc($result)) {
                $weather[] = $row;
            }
        }
    }
}

// this line is for delete the older data record than 7 days  form the database
$delete_query = "DELETE FROM datatable WHERE Date < DATE_SUB(NOW(), INTERVAL 7 DAY)";
$conn->query($delete_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather App</title>
    <style>
     #container {
        width: 700px;
        margin: 20px auto;
        background-color: none;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    h1 {
        text-align: center;
        color: #333;
    }
    .detail {
        margin-bottom: 20px;
    }
    #details {
        width:40%;
        padding: 10px;
        background-color: #f0f0f0;
        border-radius: 5px;
        
    }
    p {
        margin: 5px 0;
    }
    body{
        background-image: url("https://wallpapercave.com/wp/yRzA3uZ.jpg");
    }
    #store{
        background-color: black;
        color: white;
        padding:9px;
        border-radius:20px;
        text-decoration: none;
    
    }
    
    #touch{
        background-color: black;
        color: white;
        padding:9px;
        border-radius:20px;

    }
    #touch:hover{
        background-color:red;

    }
    input[type="text"] {
            padding: 10px; 
            margin: 5px; 
            font-size: 16px; 
            border: 1px solid #ccc; 
            border-radius: 5px; 
            width:20%;
        }
       

form {
    display: flex;
    flex-direction: column;
    align-items: center;
}

input[type="text"] {
    padding: 10px;
    margin: 5px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
}

input[type="submit"] {
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    border-radius: 5px;
    background-color: #4CAF50;
    color: white;
    cursor: pointer;
    
} 
footer {
            text-align: center;
            padding:2px;
            background-color: #333;
            color: #fff;
            background-color: #4CAF50;
            bottom: 0;
            width: 100%;
        }

    </style>
</head>

<body>
<h1>7-Days Forecast</h1>
    <form method="post" action="" class="form">
        <div id="row">
            <a id="store" href="index.html">Back</a>
            </div>
        <input type="text" name="boxinput">
        <input type="submit">
        
    </form>
    <div id="container">
        
        <div class="sub-container">
            <?php
            $previous_date = null;
            foreach ($weather as $day):
                $current_date = date('l, F j, Y', strtotime($day['date']));
                if ($current_date != $previous_date):
                    if ($previous_date !== null):
                        echo '</div>'; // Close the previous day's data container
                    endif;
            ?>
            <div id="details" class="detail">
                <h3><?php echo $current_date; ?></h3>
                <div id="dets">
                    <p><i>City:</i> <?php echo $day['cityName']; ?></p>
                    <p><i>Temperature:</i> <?php echo $day['Temperature']; ?>°C</p>
                    <p><i>Humidity:</i> <?php echo $day['Humidity']; ?>%</p>
                    <p><i>Pressure:</i> <?php echo $day['Pressure']; ?>hPa</p>
                    <p><i>Wind Speed:</i> <?php echo $day['WindSpeed']; ?>m/s</p>
                </div>
            <?php
                endif;
                $previous_date = $current_date;
            endforeach;
            if (!empty($weather)):
                echo '</div>'; // Close the last day's data container
            endif;
            ?>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Weather App. All rights reserved.</p>
    </footer>
</body>

</html>