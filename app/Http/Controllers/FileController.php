<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function download($filepath)
    {
        $path = public_path($filepath);
        $path2 = str_replace('/', '\\', $path);
        if (!File::exists($path2)) {
            abort(404);
        }
        return response()->download($path2);
    }
}
