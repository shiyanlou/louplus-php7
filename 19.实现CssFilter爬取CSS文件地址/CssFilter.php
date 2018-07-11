<?php

declare(strict_types=1);

namespace WebsiteCrawler\Filter;

use Symfony\Component\DomCrawler\Crawler;
use WebsiteCrawler\Context;
use WebsiteCrawler\Exception\InvalidWalkerException;

class CssFilter implements FilterInterface
{
    use FilterTrait;

    const TYPE = 'css';

    /**
     * {@inheritdoc}
     */
    public function filter(Context $context): array
    {
        $walker = $context->getWalker();
        if (!$walker instanceof Crawler) {
            throw new InvalidWalkerException();
        }

        $elements = $walker->filter('link');

        $result = [];
        foreach ($elements as $element) {
            $cssLink = $element->getAttribute('src');
            if (!empty($cssLink)) {
              $result[] = $cssLink;
            }
        }

        return $result;
    }
}
