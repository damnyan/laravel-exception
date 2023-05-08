<?php

namespace Tests\Example\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TestController extends Controller
{
    use ValidatesRequests;

    /**
     * Test route
     *
     * @param Request $request
     *
     * @return void
     */
    public function test(Request $request): void
    {
        $this->validate($request, ['field' => 'required']);
    }
}
