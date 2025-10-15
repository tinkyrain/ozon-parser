<?php

namespace App\Infrastructure\Selenium;

use App\Domain\Parser\ParserInterface;
use App\Infrastructure\Connector\Selenium\SeleniumConnector;
use Exception;
use Faker\Factory;

abstract class BaseSeleniumParsingProcess
{
    protected ParserInterface $parser;
    protected bool $headless = false;
    protected array $browserOptions = [
        '--no-sandbox',
        '--disable-dev-shm-usage',
        '--disable-gpu',
        '--disable-infobars',
        '--enable-javascript',
        '--disable-extensions',
        '--start-maximized',
        '--window-size=1920,1080',
        '--ignore-certificate-errors',
        '--ignore-ssl-errors',
        '--no-default-browser-check',
        '--disable-blink-features=AutomationControlled',
        '--user-agent=Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    ];
    //''
    protected array $capabilityParams = [
        'acceptInsecureCerts' => true,
        'pageLoadStrategy' => 'normal',
        'excludeSwitches' => ['enable-automation', 'load-extension'],
        'useAutomationExtension' => false,
    ];

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $seleniumConnector = new SeleniumConnector(
            $_ENV['SELENIUM_HOST'],
            $this->headless,
            $this->browserOptions,
            $this->capabilityParams
        );
        $this->parser = new SeleniumParser($seleniumConnector);
    }
}
