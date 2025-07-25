<?php

namespace App\Http\Controllers;

use App\Models\RefStatusPemeriksaan;
use Illuminate\Http\Request;

class RefStatusPemeriksaanController extends Controller
{
    public function index()
    {
        $statuses = RefStatusPemeriksaan::all(['id', 'nama']);
        return response()->json($statuses);
    }
} 