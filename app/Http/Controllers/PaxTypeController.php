<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaxTypeRequest;
use App\Http\Requests\UpdatePaxTypeRequest;
use App\Models\PaxType;

class PaxTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePaxTypeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePaxTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaxType  $paxType
     * @return \Illuminate\Http\Response
     */
    public function show(PaxType $paxType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaxType  $paxType
     * @return \Illuminate\Http\Response
     */
    public function edit(PaxType $paxType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePaxTypeRequest  $request
     * @param  \App\Models\PaxType  $paxType
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePaxTypeRequest $request, PaxType $paxType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PaxType  $paxType
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaxType $paxType)
    {
        //
    }
}
