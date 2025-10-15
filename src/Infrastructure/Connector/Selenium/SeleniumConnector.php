<?php

namespace App\Infrastructure\Connector\Selenium;

use App\Domain\Exceptions\Selenium\SeleniumConnectionException;
use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;

class SeleniumConnector
{
    private RemoteWebDriver $driver;

    /**
     * @param string|null $host
     * @param bool $headless
     * @param array $browserOptions
     * @param array $capabilityParams
     * @param int $connectionTimeout
     * @param int $requestTimeout
     * @throws Exception
     */
    public function __construct(
        private readonly ?string $host = null,
        private readonly bool $headless = true,
        private readonly array $browserOptions = [],
        private readonly array $capabilityParams = [],
        private readonly int $connectionTimeout = 15_000,
        private readonly int $requestTimeout = 60_000
    ) {
        $this->driver = $this->createDriver();
    }

    /**
     * @return RemoteWebDriver
     * @throws SeleniumConnectionException
     */
    private function createDriver(): RemoteWebDriver
    {
        $host = $this->host ?? $_ENV['SELENIUM_HOST'];

        try {
            $capabilities = $this->buildCapabilities();

            return RemoteWebDriver::create(
                $host,
                $capabilities,
                $this->connectionTimeout,
                $this->requestTimeout,
            );
        } catch (WebDriverException $e) {
            throw new SeleniumConnectionException();
        }
    }

    /**
     * @return DesiredCapabilities
     */
    private function buildCapabilities(): DesiredCapabilities
    {
        $options = new ChromeOptions();
        $capabilities = DesiredCapabilities::chrome();

        $args = $this->headless ? ['--headless', '--no-sandbox', '--disable-dev-shm-usage'] : [];
        $args = array_merge($args, $this->browserOptions);
        $options->addArguments($args);

        foreach ($this->capabilityParams as $key => $value) {
            $capabilities->setCapability($key, $value);
        }

        $capabilities->setCapability($options::CAPABILITY, $options);

        return $capabilities;
    }

    /**
     * @return RemoteWebDriver
     */
    public function getDriver(): RemoteWebDriver
    {
        return $this->driver;
    }

    /**
     * @return void
     */
    public function quit(): void
    {
        $this->driver->quit();
    }
}
