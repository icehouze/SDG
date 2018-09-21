<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Market;
use App\Brand;
use App\Category;
use Session;


class AdminMarketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $markets = Market::all();
        return view('dashboard.markets.index', compact('markets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $brands = Brand::all();
        $categories = Category::all();
        return view('dashboard.markets.create', compact('market', 'brands', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // validate the data
        request()->validate([
            'code' => 'required|unique:markets|max:2',
            'name' => 'required',
            'state' => 'required',
            'state_code' => 'required|max:2',
            'slug' => 'required|unique:markets',
            'brand_id' => 'required|exists:brands,id'
        ]);

        $market = Market::create([
            'code' => request('code'),
            'name' => request('name'),
            'name_alt' => request('name_alt'),
            'state' => request('state'),
            'state_code' => request('state_code'),
            'cities' => request('cities'),
            'slug' => request('slug'),
            'brand_id' => request('brand_id'),
        ]);

        $categories = request('categories');
        $market->categories()->attach($categories);

        // Session::flash('success', 'The market was successfully saved!');

        // this is just a temp redirect until I create show page
        return redirect()->route('dashboard.markets.index');
        // return redirect()->route('dashboard.markets.show', $market->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $market = Market::find($id);
        return view('dashboard.markets.show', compact('market'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $market = Market::find($id);
        $brands = Brand::all();
        $categories = Category::all();

        return view('dashboard.markets.edit', compact('market', 'brands', 'categories'));

    }

    public function editMarketCategory(Market $market, Category $category) {
        $market = Market::find($id);
        // dd($market->categories);
        $categories = $market->categories;
        foreach ($categories as $category) {
            return $category;
        }
        return view('dashboard.markets.editCategory', compact('market', 'category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $market = Market::find($id);

        // validate the data
        request()->validate([
            'code' => [
                'required',
                'max:2',
                Rule::unique('markets')->ignore($market->id),
            ],
            'slug' => [
                'required',
                Rule::unique('markets')->ignore($market->id),
            ],
            'name' => 'required',
            'state' => 'required',
            'state_code' => 'required|max:2',
            'brand_id' => 'required|exists:brands,id'
        ]);

        $market->code = $request->code;
        $market->name = $request->name;
        $market->name_alt = $request->name_alt;
        $market->state = $request->state;
        $market->state_code = $request->state_code;
        $market->cities = $request->cities;
        $market->slug = $request->slug;
        $market->brand_id = $request->brand_id;

        $market->save();

        $categories = request('categories');
        $market->categories()->sync($categories);

        return redirect()->route('dashboard.markets.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
