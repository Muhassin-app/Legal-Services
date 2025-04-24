<?php

namespace App\Http\Controllers\Client;
use DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Models\Tag; 
use App\Http\Controllers\Client\BaseController;
use App\Models\TagTranslation;
use App\Models\Client;
use Illuminate\Support\Facades\Storage;

class TagController extends BaseController{
    use ApiResponser;

    public function __construct()
    {
        $code = Client::orderBy('id','asc')->value('code');
        $this->folderName = '/'.$code.'/tags';
    }

    public function store(Request $request){
        try {
            $this->validate($request, [
              'name.0' => 'required|string|max:255',
              'icon' => 'image'
             ],['name.0' => 'The default language name field is required.']);
            DB::beginTransaction();

            $tag = new Tag();

            if ($request->hasFile('icon')) {    /* upload icon file */
                $file = $request->file('icon');
                $tag->icon = Storage::disk('s3')->put($this->folderName, $file, 'public');
            }

            $tag->save();
            $language_id = $request->language_id;
            foreach ($request->name as $k => $name) {
                if($name){
                    $TagTranslation = new TagTranslation();
                    $TagTranslation->name = $name;
                    $TagTranslation->slug = Str::slug($name, '-');
                    $TagTranslation->language_id = $language_id[$k];
                    $TagTranslation->tag_id = $tag->id;
                    $TagTranslation->save();
                }
            }
            DB::commit();
            return $this->successResponse($tag, 'Tag Added Successfully.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
        try {
            $tag = Tag::with(['translations'])->where(['id' => $request->tag_id])->first();
            return $this->successResponse($tag, '');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tag $tag){
         try {
            $this->validate($request, [
              'name.0' => 'required|string|max:255',
              'icon' => 'image'
            ],['name.0' => 'The default language name field is required.']);
            DB::beginTransaction();
            $tag_id = $request->tag_id;
            $tag = Tag::where('id', $tag_id)->first();

            if ($request->hasFile('icon')) {    /* upload icon file */
                $file = $request->file('icon');
                $tag->icon = Storage::disk('s3')->put($this->folderName, $file, 'public');
            }

            $tag->save();
            $language_id = $request->language_id;
            TagTranslation::where('tag_id', $tag_id)->delete();
            foreach ($request->name as $k => $name) {
                if($name){
                    $TagTranslation = new TagTranslation();
                    $TagTranslation->name = $name;
                    $TagTranslation->slug = Str::slug($name, '-');
                    $TagTranslation->language_id = $language_id[$k];
                    $TagTranslation->tag_id = $tag->id;
                    $TagTranslation->save();
                }
            }
            DB::commit();
            return $this->successResponse($tag, 'Tag Updated Successfully.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request){
        try {
            Tag::where('id', $request->tag_id)->delete();
            TagTranslation::where('tag_id', $request->tag_id)->delete();
            return $this->successResponse([], 'Tag Deleted Successfully.');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }
}
