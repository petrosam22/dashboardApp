<?php

namespace App\Http\Controllers\api;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;

use App\Traits\ValidatesImageTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     use ValidatesImageTrait;

     public function index(Request $request)
     {
         $perPage = $request->input('per_page', 5);
         $currentPage = $request->input('page');

         $products = Product::paginate($perPage, ['*'], 'page', $currentPage);

         $data = $products->items();

         $paginator = new LengthAwarePaginator(
             $data,
             $products->total(),
             $perPage,
             $currentPage,
             ['path' => $request->url(), 'query' => $request->query()]
         );

         $result = [
             'data' => $data,
             'total' => $paginator->total(),
             'per_page' => $paginator->perPage(),
             'current_page' => $paginator->currentPage(),

         ];


         

        return DataTables::of([$result])
        ->make(true);
     }

    /**
     * Show the form for creating a new resource.
     */


    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $image= $this->validateImage($request->image , 'product');
        $product  = Product::create([
            'name'=>$request->name,
            'image'=>$image,
            'description'=>$request->description,
        ]);


        return DataTables::of([$product])
        ->addColumn('message', 'The Product Created Successfully')
        ->make(true);
    }

    /**
     * Display the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request,  $id)
    {
        $product = Product::findOrFail($id);

        if ($request->hasFile('image')) {
            $image = $this->validateImage($request->file('image'), 'product');
            $product->image = $image;
        }

        $product->name = $request->input('name', $product->name);
        $product->description = $request->input('description', $product->description);

        $product->save();

        return DataTables::of([$product])
        ->addColumn('message', 'The Product Updated Successfully')
        ->make(true);
        }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        $data = [];

        return DataTables::of([$data])
        ->addColumn('message', 'The Product Deleted Successfully')
        ->make(true);

    }

    public function forceDeleteProduct($id)
{
    $product = Product::withTrashed()->findOrFail($id);
    $product->forceDelete();
    $data = [];

    return DataTables::of([$data])
    ->addColumn('message', 'Product permanently deleted')
    ->make(true);

}

public function assignProductToUser(Request $request, $id)
{
    $user = User::findOrFail($id);
    $productId = $request->product_id;

    $user->products()->attach($productId);


    $data = [];




    return DataTables::of([$data])
    ->addColumn('message', 'Product assigned to the user successfully')
    ->make(true);
}




}
