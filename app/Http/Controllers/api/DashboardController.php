<?php

namespace App\Http\Controllers\api;
use Illuminate\Pagination\LengthAwarePaginator;

use App\Models\User;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Traits\ValidatesImageTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Yajra\DataTables\DataTables;

class DashboardController extends Controller

{
    use ValidatesImageTrait;

    public function listUsers(Request $request){
        $perPage = $request->input('per_page', 5);
        $currentPage = $request->input('page');

        $products = User::paginate($perPage, ['*'], 'page', $currentPage);

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
            'length'=>$perPage,
         ];


        return DataTables::of([$result])
        ->make(true);



    }
    public function createUser(UserRequest $request){
        $user = User::create([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'role' => $request->input('role'),
            'password' => Hash::make($request->input('password')),

        ]);
   

           return DataTables::of([$user])
        ->addColumn('message', 'User Created Successfully')
        ->make(true);



    }
    public function updateUser($id,Request $request){
        $user =User::find($id);
      
      $data = [];
        if(!$user){
            return DataTables::of([$data])
            ->addColumn('error', 'User Not Found')
            ->make(true)
            ;
        }

        $user->update($request->all());



       
            return DataTables::of([$user])
        ->addColumn('message', 'User Updated Successfully')
        ->make(true);

    }
    public function destroyUser($id){
        $user =User::findOrFail($id);
        $user->delete();

    

            return DataTables::of([$user])
            ->addColumn('message', 'User Deleted Successfully')
            ->make(true);
    



    }
    public function userInformation($id){
        $user =User::findOrFail($id);

      
        return DataTables::of([$user])
        ->make(true);

    }

    public function userProducts($id,Request $request){
        $perPage = $request->input('per_page', 5);
        $page = $request->input('page');

        $user = User::findOrFail($id);
        $userProducts = $user->products()->paginate($perPage, ['*'], 'page', $page);

        $result = [
            'products' => $userProducts->items(),
            'current_page' => $userProducts->currentPage(),
            'total_pages' => $userProducts->lastPage(),
            'total_items' => $userProducts->total(),
            'length'=>$perPage

        ];

         return DataTables::of([$result])
        ->make(true);
            }


        public function profile($id, Request $request)
        {
            $user = User::find($id);
            $data =[];

            if (!$user) {
                return DataTables::of([$data])
                ->addColumn('error', 'User Not Found')
                ->make(true)
                ;
    
            }

            // Check if the request has a new password
            if ($request->has('password')) {
                // Update the user's password
                $user->password = Hash::make($request->password);
                $user->save();

            }

            $profileData = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
            ];

            // return response()->json([
            //     'profile' => $profileData,
            //     'message' => 'User Profile and Password Updated Successfully',
            // ]);
            return DataTables::of([$profileData])
            ->addColumn('message','User Profile and Password Updated Successfully')
            ->make(true);
                }






public function listProducts(Request $request){
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
        // Add other pagination information if needed
    ];

    return DataTables::of([$result])
    ->make(true);


}


public function createProduct(ProductRequest $request){

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
public function updateProducts($id,UpdateProductRequest $request){

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
public function deleteProducts($id){
    $product = Product::findOrFail($id);
    $product->delete();


    
    $data = [];
    return DataTables::of([$data])
    ->addColumn('message', 'Product deleted successfully')
    ->make(true);

}
public function assignProductToUser(Request $request, $id){
     $user = User::findOrFail($id);
    $productId = $request->product_id;


    $user->products()->attach($productId);

   ;

    $data = [];
    return DataTables::of([$data])
    ->addColumn('message', 'Product assigned to the user successfully')
    ->make(true);
}





}
