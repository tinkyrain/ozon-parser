<?php

namespace App\Infrastructure\Selenium;

use App\Domain\Parser\ParserInterface;
use App\Infrastructure\Connector\Selenium\SeleniumConnector;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Throwable;

readonly class SeleniumParser implements ParserInterface
{
    private SeleniumConnector $connector;
    private RemoteWebDriver $driver;
    public function __construct(
        SeleniumConnector $connector
    )
    {
        $this->connector = $connector;
        $this->driver = $this->connector->getDriver();
    }

    /**
     * @param string $url
     * @return string
     * @throws NoSuchElementException
     * @throws TimeoutException
     * @throws Throwable
     */
    public function getData(string $url): string
    {
        try {
            $this->executeStealthScript();
            $this->driver->get($url);
            $this->executeRandomMouseMove();
            $this->executeRandomPageScroll();
            $this->driver->wait(10)->until(
                fn() => $this->driver->findElement(WebDriverBy::cssSelector('body'))
            );
            $html = $this->driver->getPageSource();
            $this->accessCheck($html);

            return $html;
        } catch (TimeoutException $e) {
            throw new TimeoutException('Не удалось дождаться загрузки страницы');
        } catch (NoSuchElementException $e) {
            throw new NoSuchElementException('Нет элементов на странице');
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * @return void
     */
    protected function executeStealthScript(): void
    {
        $stealthScript = "
                (function() {
                    'use strict';

                    Object.defineProperty(navigator, 'webdriver', {
                        get: () => false,
                    });

                    if (navigator.userAgentData) {
                        Object.defineProperty(navigator, 'userAgentData', {
                            get: function () {
                                return {
                                    brands: [{
                                        brand: 'Chromium',
                                        version: '126'
                                    },
                                        {
                                            brand: 'Not=A?Brand',
                                            version: '24'
                                        },
                                        {
                                            brand: 'Google Chrome',
                                            version: '126'
                                        }
                                    ],
                                    mobile: false,
                                    platform: 'Linux',
                                    getHighEntropyValues: function () {
                                        return Promise.resolve({
                                            brands: [{
                                                brand: 'Chromium',
                                                version: '126'
                                            },
                                                {
                                                    brand: 'Not=A?Brand',
                                                    version: '24'
                                                },
                                                {
                                                    brand: 'Google Chrome',
                                                    version: '126'
                                                }
                                            ],
                                            mobile: false,
                                            platform: 'Linux',
                                            platformVersion: '15.0.0',
                                            architecture: 'x86',
                                            model: '',
                                            uaFullVersion: '126.0.0.0'
                                        });
                                    }
                                };
                            },
                            configurable: false,
                            enumerable: true
                        });
                    }

                    Object.defineProperty(navigator, 'languages', {
                        get: function () {
                            return ['ru-RU', 'ru', 'en-US', 'en'];
                        },
                        configurable: false,
                        enumerable: true
                    });

                    Object.defineProperty(navigator, 'platform', {
                        get: function () {
                            return 'Linux';
                        },
                        configurable: false,
                        enumerable: true
                    });

                    Object.defineProperty(navigator, 'plugins', {
                        get: function () {
                            return [{
                                name: 'Chrome PDF Plugin',
                                filename: 'internal-pdf-viewer',
                                description: 'Portable Document Format'
                            },
                                {
                                    name: 'Chrome PDF Viewer',
                                    filename: 'mhjfbmdgcfjbbpaeojofohoefgiehjai',
                                    description: 'Portable Document Format'
                                },
                                {
                                    name: 'Native Client',
                                    filename: 'internal-nacl-plugin',
                                    description: 'Native Client Executable'
                                }
                            ];
                        },
                        configurable: false,
                        enumerable: true
                    });
                });
            ";

        $this->driver->executeScript($stealthScript);
    }

    /**
     * @return void
     */
    protected function executeRandomMouseMove(): void
    {
        $this->driver->executeScript("
                const moveEvent = new MouseEvent('mousemove', {
                    view: window,
                    bubbles: true,
                    cancelable: true,
                    clientX: " . rand(100, 500) . ",
                    clientY: " . rand(100, 500) . "
                });
                document.dispatchEvent(moveEvent);
        ");
    }

    /**
     * @return void
     */
    protected function executeRandomPageScroll(): void
    {
        $scrollSteps = rand(3, 8);
        for ($i = 0; $i < $scrollSteps; $i++) {
            $scrollY = rand(100, 800);
            $this->driver->executeScript("window.scrollTo(0, $scrollY);");
            sleep(rand(1, 3));
        }
    }

    /**
     * @param string $html
     * @return void
     */
    protected function accessCheck(string $html): void
    {
        if (str_contains($html, 'Доступ ограничен') || str_contains($html, 'Проверка безопасности'))
        {
            throw new AccessDeniedException('Ozon произвел блокировку, смените IP');
        }
    }
}
