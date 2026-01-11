<?php

namespace App\Http\Controllers;

use App\Http\Resources\PolicyResource;
use App\Models\Policy;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = Policy::all();

        return PolicyResource::collection($policies);
    }

    public function show($key)
    {
        $policy = Policy::where('key', $key)->firstOrFail();

        return new PolicyResource($policy);
    }


    public function update(Request $request, $key)
    {
        $policy = Policy::where('key', $key)->firstOrFail();

        $validator = validator($request->all(), [
            // 'name' => 'required|string|max:255',
            'content' => 'required|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_keywords' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $policy->update([
            // 'name' => $request->name,
            'content' => $request['content'],
            'seo_title' => $request->seo_title,
            'seo_description' => $request->seo_description,
            'seo_keywords' => $request->seo_keywords,
        ]);

        return new PolicyResource($policy);
    }
}
