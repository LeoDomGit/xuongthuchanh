<?php

namespace Leo\Categories\Controllers;

use App\Http\Controllers\Controller;
use Leo\Categories\Models\Categories;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories= Categories::where('id_user',Auth::id())->get();
        return Inertia::render("Categories/Index",['categories'=>$categories]);
    }

    public function api_index(Categories $categories,Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['check' => false, 'msg' => $validator->errors()->first()]);
        }
        return response()->json(Categories::active()->where('id_user',$request->id_user)->orderBy('id','asc')->get());
    }

    public function api_show(Categories $categories, $id,Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['check' => false, 'msg' => $validator->errors()->first()]);
        }
        return response()->json(Categories::active()->where('id_user',$request->id_user)->where('slug',$id)->with('products.gallery')->get());
    }
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories,name',
            'id_parent'=>'exists:categories,id'
        ], [
            'name.required' => 'Chưa nhận được loại tài khoản',
            'name.unique' => 'Loại tài khoản bị trùng',
        ]);
        if ($validator->fails()) {
            return response()->json(['check' => false, 'msg' => $validator->errors()->first()]);
        }
        $data = $request->all();
        $data['id_user']=Auth::id();
        $data['slug']= Str::slug($request->name);
        Categories::create($data);
        $categories= Categories::where('id_user',Auth::id())->get();
        return response()->json(['check'=> true,'data'=> $categories]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Categories $categories)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Categories $categories)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Categories $categories,$id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'unique:categories,name',
            'id_parent'=>'exists:categories,id'
        ], [
            'name.required' => 'Chưa nhận được loại tài khoản',
            'name.unique' => 'Loại tài khoản bị trùng',
        ]);
        if ($validator->fails()) {
            return response()->json(['check' => false, 'msg' => $validator->errors()->first()]);
        }
        $data = $request->all();
        if($request->has('name')){
            $data['slug']= Str::slug($request->name);
        }
        Categories::where('id',$id)->where('id_user',Auth::id())->update($data);
        $categories=Categories::where('id_user',Auth::id())->get();
        return response()->json(['check'=> true,'data'=> $categories]);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Categories $categories, $id)
    {
        $category = Categories::find($id);
        if(!$category){
            return response()->json(['check'=> true,'msg'=>'Không tìm được loại sản phẩm']);
        }
        $category->delete();
        $categories=Categories::where('id_user',Auth::id())->get();
        return response()->json(['check'=> true,'data'=> $categories]);
    }
}
