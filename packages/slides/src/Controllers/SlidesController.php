<?php

namespace Leo\Slides\Controllers;

use Leo\Slides\Models\Slides;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class SlidesController 
{
    /**
     * Display a listing of the resource.
     */
    protected $model;
    public function __construct()
    {
        $this->model = Slides::class;
    }
    /**
     * Display a listing of the resource.
    */

    public function index()
    {
        $slides=Slides::where('id_user',Auth::id())->select('id','name','desktop','mobile','slug','url','status','created_at')->get();
        return Inertia::render('Slides/Index',['dataSlides'=>$slides]);
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
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'desktop' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'mobile' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['check' => false, 'msg' => $validator->errors()->first()]);
        }
    
        $mobile = $request->file('mobile');
        $desktop = $request->file('desktop');
    
        $mobile_file_name = $mobile->getClientOriginalName();
        $mobile->storeAs('/public/slides', $mobile_file_name);
    
        $desktop_file_name = $desktop->getClientOriginalName();
        $desktop->storeAs('/public/slides', $desktop_file_name);
    
        $slide = Slides::create([
            'name' => $request->name,
            'slug' => str::slug($request->name),
            'url' => $request->url, 
            'desktop' => $desktop_file_name,
            'id_user'=>Auth::user()->id,
            'mobile' => $mobile_file_name,
            'created_at' => now()
        ]);
    
        $slides = Slides::where('id_user',Auth::id())->select('id','name', 'slug', 'url', 'status', 'created_at')->get();
    
        return response()->json(['check' => true, 'data' => $slides]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Slides $slides,$id)
    {
        
        $slide=Slides::where('id_user',Auth::id())->where('id',$id)->first();
        return response()->json(['slide'=>$slide]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Slides $slides)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function api_index(Request $request){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['check' => false, 'msg' => $validator->errors()->first()]);
        }
        $result = Slides::active()->where('id_user',$request->id_user)->get();
        return response()->json($result);
    }

    public function api_single(Request $request,$slug){
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['check' => false, 'msg' => $validator->errors()->first()]);
        }
        $result = Slides::active()->where('id_user',$request->id_user)->where('slug',$slug)->get();
        return response()->json($result);
    }

    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'desktop' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'mobile' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'url' => 'nullable|url',
        ]);
        if ($validator->fails()) {
            return response()->json(['check' => false, 'msg' => $validator->errors()->first()]);
        }
        $data=$request->all();
        $item = Slides::where('id',$id)->first();
        if($request->hasFile('desktop')||$request->hasFile('mobile')){
            $mobile = $request->file('mobile');
            $desktop = $request->file('desktop');
            $mobile_file_name = $mobile->getClientOriginalName();
            $mobile->storeAs('/public/slides', $mobile_file_name);
            $data['mobile']=$mobile_file_name;
            $desktop_file_name = $desktop->getClientOriginalName();
            $data['desktop']=$desktop_file_name;
            $desktop->storeAs('/public/slides', $desktop_file_name);

            $oldDesktop= $item->desktop;
            $oldMobile= $item->mobile;

            Storage::delete('public/slides/' . $oldDesktop);
            Storage::delete('public/slides/' . $oldMobile);
        
            $mobile = $request->file('mobile');
            $desktop = $request->file('desktop');
        
           
        }
        
        if($request->has('name')){$data['slug']=Str::slug($request->name);}
        Slides::where('id',$id)->update($data);
    
        $slides = Slides::select('id','name', 'slug', 'url', 'status', 'created_at')->get();
    
        return response()->json(['check' => true, 'data' => $slides]);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slides $slides,$id)
    {
        $slide = Slides::findOrFail($id);
        if ($slide->desktop && Storage::exists('public/slides/' . $slide->desktop)) {
            Storage::delete('/public/slides/' . $slide->desktop);
        }
        if ($slide->mobile && Storage::exists('public/slides/' . $slide->mobile)) {
            Storage::delete('/public/slides/' . $slide->mobile);
        }

        $slide->delete();

        $slides = Slides::all();
        return response()->json(['check' => true, 'data' => $slides]);
    }
    
}
