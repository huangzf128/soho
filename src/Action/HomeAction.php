<?php

namespace App\Action;

use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;

use Google\Cloud\Vision\V1\Feature\Type;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Likelihood;
use Google\Cloud\Vision\V1\ImageContext;
use Google\Cloud\Vision\V1\WebDetectionParams;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Chrome\ChromeDriver;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

/**
 * Action
 */
final class HomeAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->container = $container;
    }

    private $productPath = 'images/products/';

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return $this->container->get('view')->render($response, 'index.html', []);
    }

	private function sendRequest($url, $timeout = 15){
	
		$agent = "Opera/9.80 (J2ME/MIDP; Opera Mini/4.2.14912/870; U; id) Presto/2.4.15";
		$agent = "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; CIBA)";

	    $curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FAILONERROR, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_USERAGENT, $agent);
		//SSL証明書を無視
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		if ($timeout){
			curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		}

		$res = curl_exec($curl);
		curl_close($curl);

		return $res;
	}
	
    public function reverse(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {

		$chromeOptions = new ChromeOptions();
		$chromeOptions->addArguments(['--headless']);
		
		$capabilities = DesiredCapabilities::chrome();
		$capabilities->setCapability(ChromeOptions::CAPABILITY_W3C, $chromeOptions);
		
		// Start the browser with $capabilities
		// A) When using RemoteWebDriver::create()
		// $driver = RemoteWebDriver::create($serverUrl, $capabilities);
		// B) When using ChromeDriver::start to start local Chromedriver
		$driver = ChromeDriver::start($capabilities);

		$driver.get("https://air-ship.jp");
		$section = $driver->findElement(WebDriverBy::id('hero'));
		$driver.quit();
		
		
		// $apiUrl = "https://images.google.com/searchbyimage?q=site:www.amazon.co.jp&client=app&image_url=";
		// $apiUrl = "https://images.google.com/searchbyimage?client=app&image_url=";
		// $imageRoot = "images/";

		// if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {

		// 	$path = $_FILES["image"]["tmp_name"];
		// 	move_uploaded_file($path, 'images/'. $_FILES["image"]["name"]);

		// 	$imageUrl = "https://m.media-amazon.com/images/I/61lB4DY+SXL._AC_UY606_.jpg";

		// 	$res = $this->sendRequest($apiUrl.$imageUrl);

		// }

        return $this->container->get('view')->render($response, 'result.html', ['res' => $res]);
    }


    public function search(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=D:/repo/soho/searchItem/public/resource/searchitem-395801-753ee51e2a7f.json');

		if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
			$imageAnnotator = new ImageAnnotatorClient();
			// $path = 'assets/img/glasses.jpg';
			$path = $_FILES["image"]["tmp_name"];
	
			move_uploaded_file($path, 'images/'.$_FILES["image"]["name"]);

			$path = 'images/'.$_FILES["image"]["name"];

			$results = $this->webDetection($imageAnnotator, $path);
			// $results = $this->detectLabel($imageAnnotator, $path);
		}

        return $this->container->get('view')->render($response, 'index.html', $results);
    }

    private function detectLabel(ImageAnnotatorClient $imageAnnotator, $path)
    {
        # annotate the image
        $image = file_get_contents($path);
        $response = $imageAnnotator->labelDetection($image);
        $labs = $response->getLabelAnnotations();

		$labels = [];
        if ($labs) {
            foreach ($labs as $lab) {
				$labels[] = $lab;
            }
        }

        $imageAnnotator->close();

		return ['labels' => $labels];
    }

    private function webDetection(ImageAnnotatorClient $imageAnnotator, $path)
    {
        $image = file_get_contents($path);

        $webDetectionParams = new WebDetectionParams();
        $webDetectionParams->setIncludeGeoResults(true);

        $imageContext = new ImageContext();
        $imageContext->setWebDetectionParams($webDetectionParams);

        $res = $imageAnnotator->webDetection($image, ['imageContext' => $imageContext]);
        $web = $res->getWebDetection();

        // Print best guess labels
        $bestGuessLabels = [];
        foreach ($web->getBestGuessLabels() as $label) {
            $bestGuessLabels[] = $label;
        }

        // Print pages with matching images
        $pagesWithMatchingImages = [];
        foreach ($web->getPagesWithMatchingImages() as $page) {
			if (strpos($page->getUrl(), 'amazon') !== false) {
				$pagesWithMatchingImages[] = $page;
			}
        }

        // Print full matching images
        $fullMatchingImages = [];
        foreach ($web->getFullMatchingImages() as $fullMatchingImage) {
            $fullMatchingImages[] = $fullMatchingImage;
        }

        // Print partial matching images
        $partialMatchingImages = [];
        foreach ($web->getPartialMatchingImages() as $partialMatchingImage) {
            $partialMatchingImages = $partialMatchingImage;
        }

        // Print visually similar images
        $visuallySimilarImages = [];
        foreach ($web->getVisuallySimilarImages() as $visuallySimilarImage) {
            $visuallySimilarImages[] = $visuallySimilarImage;
        }

        // Print web entities
        $webEntities = [];
        foreach ($web->getWebEntities() as $entity) {
            if ($entity->getScore() > 0.5) {
                $webEntities[] = $entity;
            }
        }

        $imageAnnotator->close();

        return  ['bestGuessLabels' => $bestGuessLabels, 'pagesWithMatchingImages' => $pagesWithMatchingImages,
                 'fullMatchingImages' => $fullMatchingImages, 'partialMatchingImages' => $partialMatchingImages,
                 'visuallySimilarImages' => $visuallySimilarImages, 'webEntities' => $webEntities
                ];
    }



}
