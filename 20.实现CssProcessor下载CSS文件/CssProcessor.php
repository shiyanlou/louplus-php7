<?php

declare(strict_types=1);

namespace App\WebsiteCrawler\Processor;

use Http\Client\Exception\RequestException;
use Illuminate\Contracts\Filesystem\Filesystem;
use League\Uri;
use Psr\Http\Message\UriInterface;
use WebsiteCrawler\Context;
use WebsiteCrawler\Result;
use WebsiteCrawler\Processor\ProcessorInterface;
use WebsiteCrawler\Processor\ProcessorTrait;

class CssProcessor implements ProcessorInterface
{
    use ProcessorTrait;

    const TYPE = 'css';

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Result $result, Context $context): void
    {
        foreach ($this->normalizeUris($result->getUri(), $result->get($this->getType())) as $cssUri) {
            try {
                $response = $context->getHttpClient()->sendRequest(
                    $context->getMessageFactory()->createRequest('GET', $cssUri)
                );
            } catch (RequestException $e) {
                // Workaround against incorrect requests (Malformed url, etc), just skip them.
                continue;
            }
            if (200 !== $response->getStatusCode()) {
                continue;
            }

            $this->filesystem->put(
                sprintf('%s/%s', $result->getUri()->getHost(), $this->normalizeFileName($cssUri)),
                $response->getBody()
            );
        }
    }

    /**
     * @param UriInterface $baseUri
     * @param array $uris
     *
     * @return array
     */
    private function normalizeUris(UriInterface $baseUri, array $uris): array
    {
        foreach ($uris as $idx => $uri) {
            // Converts relative uris to absolute with $baseUri schema and host.
            $uris[$idx] = (string) Uri\create($uri, $baseUri);
        }

        return $uris;
    }

    /**
     * Solves linux FS special character issue.
     *
     * @param string $filename
     *
     * @return string
     */
    private function normalizeFileName(string $filename): string
    {
        return mb_ereg_replace('/', '__', $filename);
    }
}
