<?php

namespace eLife\Journal\Helper;

use Symfony\Component\HttpKernel\UriSigner;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UnexpectedValueException;

final class DownloadLinkUriGenerator
{
    const URL_UNSAFE_CHARS = '+/=';
    const URL_UNSAFE_REPLACES = '._-';

    private $urlGenerator;
    private $uriSigner;

    public function __construct(UrlGeneratorInterface $urlGenerator, UriSigner $uriSigner)
    {
        $this->urlGenerator = $urlGenerator;
        $this->uriSigner = $uriSigner;
    }

    public function generate(DownloadLink $link) : string
    {
        $uri = $this->urlGenerator->generate('download', ['uri' => strtr(base64_encode($link->getUri()), self::URL_UNSAFE_CHARS, self::URL_UNSAFE_REPLACES), 'name' => $link->getFilename()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->uriSigner->sign($uri);
    }

    public function check(string $uri) : DownloadLink
    {
        if (!$this->uriSigner->check($uri)) {
            throw new UnexpectedValueException('Not a valid signed URI');
        }

        $path = explode('/', parse_url($uri, PHP_URL_PATH));

        $name = array_pop($path);
        $uri = array_pop($path);

        return new DownloadLink(base64_decode(strtr($uri, self::URL_UNSAFE_REPLACES, self::URL_UNSAFE_CHARS)), $name);
    }
}
