<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $x = $this->visit('https://www.mctimoney-chiropractic.org/ChiroSearch.aspx?txtSearch=AL5&Human=True&Animal=False');
        $x->see('Julia Sayers');
    }
}
