<?php

namespace Chuoke\Blog\Support;

use Hidehalo\Nanoid\Client as NanoidClient;

class Nanoid
{
    protected ?NanoidClient $client = null;

    public function __construct(
        protected int $length = 10,
        protected string $alphabet = '0123456789abcdefghijklmnopqrstuvwxyz'
    ) {
    }

    public function getClient(): NanoidClient
    {
        if (! $this->client) {
            $this->client = new NanoidClient();
        }

        return $this->client;
    }

    public function generate(): string
    {
        return $this->getClient()->formattedId($this->alphabet, $this->length);
    }
}
