<?php

namespace App\Application\Process;

use App\Application\DTO\Request\ParseProductRequestDTO;
use App\Application\DTO\Response\ParseProductResponseDTO;
use App\Domain\DTO\Request\RequestDTO;
use App\Domain\DTO\Response\ResponseDTO;
use App\Domain\Process\ProcessInterface;
use App\Infrastructure\Selenium\BaseSeleniumParsingProcess;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\TimeoutException;
use Symfony\Component\DomCrawler\Crawler;
use Throwable;

class ProductParsingProcess extends BaseSeleniumParsingProcess implements ProcessInterface
{
    /**
     * @param ParseProductRequestDTO $requestDTO
     * @return ResponseDTO
     * @throws NoSuchElementException
     * @throws TimeoutException
     * @throws Throwable
     */
    public function run(RequestDTO $requestDTO): ResponseDTO
    {
        $html = $this->parser->getData($requestDTO->getUrl());
        $crawler = new Crawler($html);

        $title = $this->extractTitle($crawler);
        $categoryPath = $this->extractCategories($crawler);
        $characteristics = $this->extractCharacteristics($crawler);
        $article = $this->extractArticle($characteristics);
        $type = $characteristics['Тип'] ?? null;
        $country = $characteristics['Страна'] ?? $characteristics['Страна-изготовитель'] ?? null;
        $description = $this->extractDescription($crawler);
        $images = $this->extractImages($crawler);

        return new ParseProductResponseDTO(
            $title,
            $categoryPath,
            $article,
            $type,
            $country,
            $description,
            $images,
            $characteristics,
        );
    }

    /**
     * @param Crawler $crawler
     * @return string|null
     */
    private function extractTitle(Crawler $crawler): ?string
    {
        return $crawler->filter('[data-widget="webProductHeading"] h1')->count()
            ? trim($crawler->filter('[data-widget="webProductHeading"] h1')->text())
            : null;
    }

    /**
     * @param Crawler $crawler
     * @return string
     */
    private function extractCategories(Crawler $crawler): string
    {
        $categories = $crawler->filter('[data-widget="breadCrumbs"] ol li span')
            ->each(fn(Crawler $node) => trim($node->text()));
        return implode(' / ', $categories);
    }

    /**
     * @param Crawler $crawler
     * @return array
     */
    private function extractCharacteristics(Crawler $crawler): array
    {
        $characteristics = [];
        $crawler->filter('#section-characteristics dl')->each(function (Crawler $node) use (&$characteristics) {
            $nameNode = $node->filterXPath('.//dt');
            $valueNode = $node->filterXPath('.//dd');
            if ($nameNode->count() && $valueNode->count()) {
                $name = trim($nameNode->text());
                $value = trim(preg_replace('/\s+/', ' ', $valueNode->text()));

                if ($name !== '' && $value !== '') {
                    $characteristics[$name] = $value;
                }
            }
        });

        return $characteristics;
    }

    /**
     * @param array $characteristics
     * @return string|null
     */
    private function extractArticle(array $characteristics): ?string
    {
        return $characteristics['Артикул']
            ?? $characteristics['Партномер']
            ?? null;
    }

    /**
     * @param Crawler $crawler
     * @return string|null
     */
    private function extractDescription(Crawler $crawler): ?string
    {
        if (!$crawler->filter('#section-description')->count()) {
            return null;
        }

        $description = $crawler->filter('#section-description')
            ->filter('div span')
            ->each(fn(Crawler $node) => trim($node->text()));

        $description = array_filter($description, fn($t) => $t !== '');
        return implode("\n", $description);
    }

    /**
     * @param Crawler $crawler
     * @return array
     */
    private function extractImages(Crawler $crawler): array
    {
        $images = [];
        $crawler->filter('[data-widget="webGallery"] img')->each(function (Crawler $node) use (&$images) {
            $src = $node->attr('src');
            $srcset = $node->attr('srcset');
            $alt = $node->attr('alt') ?? null;

            if ($src) {
                $images[] = [
                    'small' => $src,
                    'large' => $this->extractLargestFromSrcset($srcset) ?? $src,
                    'alt' => $alt,
                ];
            }
        });
        return $images;
    }

    /**
     * @param string|null $srcset
     * @return string|null
     */
    private function extractLargestFromSrcset(?string $srcset): ?string
    {
        if (!$srcset) return null;
        $parts = explode(' ', trim($srcset));
        foreach ($parts as $part) {
            if (str_contains($part, 'wc1000') || str_contains($part, '2x')) {
                return trim(str_replace('2x', '', $part));
            }
        }
        return $parts[0] ?? null;
    }
}
