<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudyPlanController extends Controller
{
    public function index()
    {
        return view('study-plans.index');
    }

    public function create()
    {
        return view('study-plans.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('study-plans.index')->with('success', 'Study plan created successfully!');
    }

    public function show($id)
    {
        return view('study-plans.show', compact('id'));
    }

    public function edit($id)
    {
        return view('study-plans.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('study-plans.index')->with('success', 'Study plan updated successfully!');
    }

    public function destroy($id)
    {
        return redirect()->route('study-plans.index')->with('success', 'Study plan deleted successfully!');
    }
}
