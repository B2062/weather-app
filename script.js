// this line is for define  CONSTANT for default city, api and url
const DEFAULT_CITY = 'Rewa';
const API_BASE_URL = 'https://api.openweathermap.org/data/2.5/weather';
const API_KEY = '2bcae1a123d7709d70bb7e9c3677dc0f';

//this function is to retrieve weather data for a given city
async function getWeatherData(city) {
    //retrieve stored weather data from localstorage
    const storedWeatherData = JSON.parse(localStorage.getItem(city));
    //Get current time.
    const currentTime = new Date().getTime();
    //it is used to define a constant for a one day in miliseconds
    const oneDay = 24 * 60 * 60 * 1000; 
    
    //this is for checking if the stored data is exist within one day old
    if (storedWeatherData && currentTime - storedWeatherData.timestamp < oneDay) {
        //now  if so, display stored weather data.
        displayWeatherInfo(storedWeatherData.data);
        return;
    }

    try {
        //if no stored data in localstorage, so this line if for fetch new data from the API.
        const apiUrl = `${API_BASE_URL}?q=${encodeURIComponent(city)}&appid=${API_KEY}&units=metric`;
        const response = await fetch(apiUrl);
        if (!response.ok) throw new Error('Failed to fetch weather data');

        const data = await response.json();
        //store fetched data in localstorage
        localStorage.setItem(city, JSON.stringify({ timestamp: currentTime, data: data }));
        //display fetch weather data
        displayWeatherInfo(data);
    } catch (error) {
        //its line is for handle the errors during fetching or parsing data
        console.error('Error fetching weather data:', error);
        displayErrorMessage('This is an unexpected error, please try again later.');
    }
}
// this function is for displaying weatherinformation that has a parameter named data 
function displayWeatherInfo(data) {
    //this line is for retrive the hmtl with id 'weather' and assign it to the variable weatherinfoDiv 
    const weatherInfoDiv = document.getElementById('weather');
    //this line is for retrive the html element with the class 'detail' and  asign it to the variable detailDiv
    const detailDiv = document.querySelector('.detail');

    // Check if the data object is falsy
    if (!data || !data.name || !data.main || !data.weather || !data.wind) {
        // If any required property is missing or if data is falsy, display error
        displayErrorMessage('Invalid weather data received.');
        return;
    }
    // Extract relavent information from data
    const { name, main, weather, wind } = data;
    const temperature = Math.round(main.temp);
    const weatherConditions = weather.map(condition => condition.description).join(', ');
    const iconCode = weather[0].icon;
    const iconUrl = `http://openweathermap.org/img/wn/${iconCode}.png`; // Icon URL

    // Display weather inofromation on the webpage
    weatherInfoDiv.innerHTML = `
        <h1 class="temperature">${temperature}°C</h1>
        <h1 class="city">${name}</h1>
        <p class="weather-condition">${weatherConditions}</p>
        <img src="${iconUrl}" alt="Weather Icon"> <!-- Display Weather Icon -->
    `;

    detailDiv.innerHTML = `
        <div>
            <p class="wind">${wind.speed} m/s</p>
            <p>Wind Speed</p>
        </div>
        <div>
            <p class="humidity">${main.humidity}%</p>
            <p>Humidity</p>   
        </div>
        <div>
            <p class="pressure">${main.pressure} hPa</p>
            <p>Pressure</p>
        </div>
    `;
}
// this function is to display the error message on the webpage.
function displayErrorMessage(message) {
    const weatherInfoDiv = document.getElementById('weather');
    weatherInfoDiv.innerHTML = `<p class="error-message">${message}</p>`;
    document.querySelector('.detail').innerHTML = '';
}
// Event listener for keypress on city input field
document.getElementById('cityInput').addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
        searchWeather();
    }
});
// Function to initiate weather search based on user input
function searchWeather() {
    const city = document.getElementById('cityInput').value.trim();
    if (city) getWeatherData(city);
}

document.addEventListener('DOMContentLoaded', () => {
    getWeatherData(DEFAULT_CITY);
});
