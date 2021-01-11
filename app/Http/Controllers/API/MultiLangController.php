<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\MultiLang;
use App\Model\AnnouceMultiLang;

class MultiLangController extends Controller
{
    //

    public function getLang($key) {
        $param = $key.'_title';
        $multiLang = MultiLang::select(['id', $param, 'category'])->orderBy('category_id', 'asc')->orderBy('ID', 'asc')->get();
        $category = MultiLang::select(['category'])->groupBy('category')->pluck('category');
        $results = [];

        for ($i=0; $i < count($category); $i++) { 
            $results[$category[$i]] = [];
        }

        foreach ($multiLang as $key => $value) {
            // if($value->category == $value->category){
                $results[$value->category][] = $value->$param;
            // }
        }

        return $this->response(200, [
            'web' => $results,
        ]);
    }

    public function getAnnouceLang($key){
        $param = $key.'_title';
        $annouceMultiLang = AnnouceMultiLang::select(['id', $param, 'category'])->orderBy('category_id', 'asc')->get();
        $annouceCategory = AnnouceMultiLang::select(['category'])->groupBy('category')->pluck('category');
        $annouceResults = [];

        for($i = 0; $i < count($annouceCategory); $i++){
            $annouceResults[$annouceCategory[$i]] = [];
        }

        foreach ($annouceMultiLang as $key => $value) {
            // if($value->category == $value->category){
                $annouceResults[$value->category][] = $value->$param;
            // }
        }

        return $this->response(200, [
            'annouce' => $annouceResults,
        ]);

    }

}
