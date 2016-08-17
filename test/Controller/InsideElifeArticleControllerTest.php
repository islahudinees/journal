<?php

namespace test\eLife\Journal\Controller;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

final class InsideElifeArticleControllerTest extends PageTestCase
{
    /**
     * @test
     */
    public function it_displays_an_inside_elife_page()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', $this->getUrl());

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Blog article title', $crawler->filter('.content-header__title')->text());
    }

    /**
     * @test
     */
    public function it_displays_a_404_if_the_article_is_not_found()
    {
        $client = static::createClient();

        static::mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/blog-articles/1',
                [
                    'Accept' => 'application/vnd.elife.blog-article+json; version=1',
                ]
            ),
            new Response(
                404,
                [
                    'Content-Type' => 'application/problem+json',
                ],
                json_encode([
                    'title' => 'Not found',
                ])
            )
        );

        $client->request('GET', '/inside-elife/1');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    protected function getUrl() : string
    {
        $this->mockApiResponse(
            new Request(
                'GET',
                'http://api.elifesciences.org/blog-articles/1',
                ['Accept' => 'application/vnd.elife.blog-article+json; version=1']
            ),
            new Response(
                200,
                ['Content-Type' => 'application/vnd.elife.blog-article+json; version=1'],
                json_encode([
                    'id' => '1',
                    'title' => 'Blog article title',
                    'published' => '2010-01-01T00:00:00+00:00',
                    'impactStatement' => 'Blog article impact statement',
                    'content' => [
                        [
                            'type' => 'paragraph',
                            'text' => 'Blog article text.',
                        ],
                    ],
                ])
            )
        );

        return '/inside-elife/1';
    }
}