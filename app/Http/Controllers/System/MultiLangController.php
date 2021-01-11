<?php

namespace App\Http\Controllers\System;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\MultiLang;
use App\Model\LangVersion;
use App\Model\AnnouceMultiLang;

class MultiLangController extends Controller
{
    //

    public function __construct(){
        $this->middleware('login');
    }

    public function getLanguageTranslation(Request $request){
        $multiLang = MultiLang::orderBy('category', 'ASC')->orderBy('category_id', 'asc');
        
        $langVersion = LangVersion::all();

        if(isset($request->vi_title)){
            $multiLang = $multiLang->where('vi_title', 'LIKE' ,'%'.$request->vi_title.'%');
        }

        if(isset($request->en_title)){
            $multiLang = $multiLang->where('en_title', 'LIKE' ,'%'.$request->en_title.'%');
        }

        if(isset($request->category)){
            $multiLang = $multiLang->where('category', $request->category);
        }

        $multiLang = $multiLang->paginate(25);

        return view('System.Admin.language_translation', [
            'multiLang' => $multiLang,
            'lang' => [],
            'lang_version' => $langVersion
        ]);
    }

    public function postLanguageTranslation(Request $request){
        $multiLang = MultiLang::find($request->id);
        if(!$multiLang) {
            $multiLang = new MultiLang();
        }
        $categoryNumber = MultiLang::where('category', $request->category)->orderBy('ID', 'ASC')->get();
        $multiLang->vi_title = $request->vi_title;
        $multiLang->category_id = $categoryNumber[count($categoryNumber) - 1]->category_id + 1;
        $multiLang->en_title = $request->en_title;
        $multiLang->ja_title = $request->ja_title;
        $multiLang->cn_title = $request->cn_title;
        $multiLang->kr_title = $request->kr_title;
        $multiLang->ru_title = $request->ru_title;
        $multiLang->es_title = $request->es_title;
        $multiLang->category = $request->category;
        $multiLang->save();
        return redirect()->route('admin.getLanguageTranslation');
    }

    public function deleteLanguageTranslation($id){
        $multiLang = MultiLang::find($id);

        if($multiLang){
            $multiLang->delete();
        }

        return redirect()->back();
    }

    public function editLanguageTranslation($id){
        $lang = MultiLang::find($id);
        $multiLang = MultiLang::orderBy('category', 'ASC')->orderBy('category_id', 'asc');
        $langVersion = LangVersion::all();

        $multiLang = $multiLang->paginate(25);

        if($lang){
            return view('System.Admin.language_translation', [
                'multiLang' => $multiLang,
                'lang' => $lang,
                'lang_version' => $langVersion
            ]);
        }

        return redirect()->back();
    }

    public function changeVersionLanguage(Request $request){
        foreach ($request->except('_token') as $key => $value) {
            # code...
            $lang = LangVersion::where('key', $key)->first();
            $lang->version = $value;
            $lang->save();
        }

        return redirect()->back();
    }

    public function getAnnouncementLanguageTranslation(Request $request){
        $multiLang = AnnouceMultiLang::orderBy('category', 'ASC')->orderBy('category_id', 'asc');
        
        $langVersion = LangVersion::all();

        if(isset($request->vi_title)){
            $multiLang = $multiLang->where('vi_title', 'LIKE' ,'%'.$request->vi_title.'%');
        }

        if(isset($request->en_title)){
            $multiLang = $multiLang->where('en_title', 'LIKE' ,'%'.$request->en_title.'%');
        }

        if(isset($request->category)){
            $multiLang = $multiLang->where('category', $request->category);
        }

        $multiLang = $multiLang->paginate(25);

        return view('System.Admin.annoucement_language_translation', [
            'multiLang' => $multiLang,
            'lang' => [],
            'lang_version' => $langVersion
        ]);
    }

    public function postAnnouncementLanguageTranslation(Request $request){
        $multiLang = AnnouceMultiLang::find($request->id);
        if(!$multiLang) {
            $multiLang = new AnnouceMultiLang();
        }
        $multiLang->vi_title = $request->vi_title;
        $multiLang->category_id = $request->category_id;
        $multiLang->en_title = $request->en_title;
        $multiLang->ja_title = $request->ja_title;
        $multiLang->cn_title = $request->cn_title;
        $multiLang->kr_title = $request->kr_title;
        $multiLang->ru_title = $request->ru_title;
        $multiLang->es_title = $request->es_title;
        $multiLang->category = $request->category;
        $multiLang->save();
        return redirect()->route('admin.getAnnouncementLanguageTranslation');
    }

    public function deleteAnnouncementLanguageTranslation($id){
        $multiLang = AnnouceMultiLang::find($id);

        if($multiLang){
            $multiLang->delete();
        }

        return redirect()->back();
    }

    public function editAnnouncementLanguageTranslation($id){
        $lang = AnnouceMultiLang::find($id);
        $multiLang = AnnouceMultiLang::orderBy('category', 'ASC')->orderBy('category_id', 'asc');
        $langVersion = LangVersion::all();

        $multiLang = $multiLang->paginate(25);

        if($lang){
            return view('System.Admin.annoucement_language_translation', [
                'multiLang' => $multiLang,
                'lang' => $lang,
                'lang_version' => $langVersion
            ]);
        }

        return redirect()->back();
    }
}
