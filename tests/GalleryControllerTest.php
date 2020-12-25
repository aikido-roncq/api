<?php

namespace Tests;

class GalleryControllerTest extends ControllerTest
{
    protected const BASE_URI = '/gallery';
    protected const KEYS = ['src', 'added', 'caption'];
    protected const PK = 'src';

    // ========================================================================
    // GET /
    // ========================================================================

    public function testGetAll()
    {
        [$code, $images] = $this->get(self::BASE_URI);

        $this->assertEquals(200, $code);
        $this->assertNotEquals([], $images);

        foreach ($images as $image)
            $this->verifyKeys($image);
    }

    public function testSortedByAddedDate()
    {
        [, $images] = $this->get(self::BASE_URI);

        $dates = array_map(function ($image) {
            return $image['added'];
        }, $images);

        $sorted = $dates;
        sort($dates);

        $this->assertEquals($sorted, $dates);
    }

    // ========================================================================
    // POST /
    // ========================================================================
    public function testPostWhenLoggedOut()
    {
        $this->expectExceptionCode(401);
        $this->client->post(self::BASE_URI);
    }
}
