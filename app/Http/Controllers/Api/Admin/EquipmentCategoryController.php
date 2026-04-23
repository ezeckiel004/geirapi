<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EquipmentCategoryController extends Controller
{
    public function index()
    {
        $categories = EquipmentCategory::orderBy('name')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => 'required|string|unique:equipment_categories,code|max:50|regex:/^[a-z_]+$/',
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $category = EquipmentCategory::create($data);

        return response()->json($category, 201);
    }
}