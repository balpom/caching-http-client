<?php

namespace Balpom\CachingHttpClient;

use Psr\Http\Client\ClientInterface;
use Psr\SimpleCache\CacheInterface;
use Balpom\HttpCacheTtl\TtlContainer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CachingHttpClient implements ClientInterface
{
    private ClientInterface $client;
    private CacheInterface $cache;
    private TtlContainer $times;

    public function __construct(
            ClientInterface $client,
            CacheInterface $cache,
            TtlContainer $times
    )
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->times = $times;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $key = $this->getKey($request);
        if ($this->cache->has($key)) {
            return $this->cache->get($key);
        }

        $response = $this->httpClient->sendRequest($request);
        $code = $response->getStatusCode();
        $ttl = $this->times->getTtl($code);
        $this->cache->set($key, $response, $ttl);

        return $response;
    }

    private function getKey(RequestInterface $request)
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->__toString();

        return $method . '_' . $uri;
    }

}
