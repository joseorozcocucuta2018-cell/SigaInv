<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreFormulaTransformacionRequest;
use App\Http\Requests\UpdateFormulaTransformacionRequest;
use App\Models\FormulaTransformacion;

class FormulaTransformacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFormulaTransformacionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FormulaTransformacion $formulaTransformacion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FormulaTransformacion $formulaTransformacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFormulaTransformacionRequest $request, FormulaTransformacion $formulaTransformacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FormulaTransformacion $formulaTransformacion)
    {
        //
    }
}
