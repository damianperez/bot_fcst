<?php
use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\Exception as OWMException;
use Http\Factory\Guzzle\RequestFactory;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;

// Must point to composer's autoload file.
require '../../vendor/autoload.php';

// Language of data (try your own language here!):

/*
Arabic - ar, 
Bulgarian - bg, 
Catalan - ca, 
Czech - cz, 
German - de, 
Greek - el, 
English - en, 
Persian (Farsi) - fa, 
Finnish - fi, 
French - fr, 
Galician - gl, 
Croatian - hr, 
Hungarian - hu, 
Italian - it, 
Japanese - ja, 
Korean - kr, 
Latvian - la, 
Lithuanian - lt, 
Macedonian - mk, 
Dutch - nl, 
Polish - pl, 
Portuguese - pt, 
Romanian - ro, 
Russian - ru, 
Swedish - se, 
Slovak - sk, 
Slovenian - sl, 
Spanish - es, 
Turkish - tr, 
Ukrainian - ua, 
Vietnamese - vi, 
Chinese Simplified - zh_cn, 
Chinese Traditional - zh_tw.


ar, 
bg,
ca, 
cz, 
de, 
el, 
en, 
fi, 
fr, 
gl, 
hr, 
hu, 
it, 
ja, 
kr, 
la, 
lt, 
mk, 
nl, 
pl, 
pt, 
ro, 
ru, 
se, 
sk, 
sl, 
es, 
tr, 
ua, 
vi, 
*/







$lang = 'es';
$lang = 'fr';
// Units (can be 'metric' or 'imperial' [default]):
$units = 'metric';

// You can use every PSR-17 compatible HTTP request factory
// and every PSR-18 compatible HTTP client. This example uses
// `http-interop/http-factory-guzzle` ^1.0 and `php-http/guzzle6-adapter` ^2.0 || ^1.0
// which you need to install separately.
$httpRequestFactory = new RequestFactory();
$httpClient = GuzzleAdapter::createWithConfig([]);

// Create OpenWeatherMap object.
$apikey="f7577c33470559de7baaee9db157f56a";
$owm = new OpenWeatherMap($apikey, $httpClient, $httpRequestFactory);
/**
     * Returns the current weather at the place you specified.
     *
     * @param array|int|string $query The place to get weather information for. For possible values see below.
     * @param string           $units Can be either 'metric' or 'imperial' (default). This affects almost all units returned.
     * @param string           $lang  The language to use for descriptions, default is 'en'. For possible values see http://openweathermap.org/current#multi.
     * @param string           $appid Your app id, default ''. See http://openweathermap.org/appid for more details.
     *
     * @throws OpenWeatherMap\Exception  If OpenWeatherMap returns an error.
     * @throws \InvalidArgumentException If an argument error occurs.
     *
     * @return CurrentWeather The weather object.
     *
     * There are four ways to specify the place to get weather information for:
     * - Use the city name: $query must be a string containing the city name.
     * - Use the city id: $query must be an integer containing the city id.
     * - Use the coordinates: $query must be an associative array containing the 'lat' and 'lon' values.
     * - Use the zip code: $query must be a string, prefixed with "zip:"
     *
     * Zip code may specify country. e.g., "zip:77070" (Houston, TX, US) or "zip:500001,IN" (Hyderabad, India)
     *
     * @api	 
	 public function getWeather($query, $units = 'imperial', $lang = 'en', $appid = '')
*/

$weather='';
$query=array();
$query['lat']="-34.90";
$query['lon']= "-58.023";

/*query['lat']= "37.39";
$query['lon']= "-122.08";
*/


try {
    $weather = $owm->getWeather($query,$units, $lang);
} catch(OWMException $e) {
    echo 'OpenWeatherMap exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').';
} catch(\Exception $e) {
    echo 'General exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').';
}




echo '<h1>getWeather</h1>';
echo '<h4><pre>'.var_export( $query ).'</pre></h4>';
echo '<pre>';
var_export( $weather );
echo '</pre>';
echo "TZ<hr />\n\n\n";
echo $weather->tz."<hr />\n\n\n";
echo $weather->weather->description;

//$img  = 'icon' => '02d',
echo 'Now:' . $weather->temperature;
echo 'Min:' . $weather->temperature->min;
echo 'Max' . $weather->temperature->max;
echo "<hr />\n\n\n";
$forecast = $owm->getWeatherForecast($query, $units, $lang, '', 5);

echo '<h1>Forecast</h1>';
echo '<h4><pre>'.var_export( $query ).'</pre></h4>';

echo '<pre>';
var_export( $forecast );
echo '</pre>';
echo '<pre>';
echo "EXAMPLE 1<hr />\n\n\n";

echo "Tz: " . $forecast->tz;
echo "City: " . $forecast->city->name;
echo "<br />\n";
echo "LastUpdate: " . $forecast->lastUpdate->format('d.m.Y H:i');
echo "<br />\n";
echo "Sunrise : " . $forecast->sun->rise->format("H:i:s (e)") . " Sunset : " . $forecast->sun->set->format("H:i:s (e)");
echo "<br />\n";
echo "<br />\n";
echo '</pre>';

echo '<pre>';
foreach ($forecast as $weather) {
    // Each $weather contains a Cmfcmf\ForecastWeather object which is almost the same as the Cmfcmf\Weather object.
    // Take a look into 'Examples_Current.php' to see the available options.
    echo "Weather forecast at " . $weather->time->day->format('d.m.Y') . " from " . $weather->time->from->format('H:i') . " to " . $weather->time->to->format('H:i');
    echo "<br />\n";
    echo $weather->temperature;
    echo "<br />\n";
    echo "Sun rise: " . $weather->sun->rise->format('d.m.Y H:i (e)');
    echo "<br />\n";
	echo '<pre>';
		var_export( $weather );
	echo '</pre>';
}
echo '</pre>';
echo '<h1>Getwheather x city</h1>';
$query=array();
$query="La Plata";
echo '<h4><pre>'.var_export( $query ).'</pre></h4>';
$weather = $owm->getWeather($query,$units, $lang);

echo '<pre>';
var_export( $weather );
echo '</pre>';



echo "Fin";
?>