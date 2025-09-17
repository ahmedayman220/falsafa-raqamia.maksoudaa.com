<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentationController extends Controller
{
    /**
     * Display the documentation page.
     */
    public function index()
    {
        return view('documentation');
    }
}