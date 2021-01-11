<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\ItemHistory;
use Validator;

class ItemHistoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $value) {
                // return $error;
                return $this->response(200, [], $value, $validator->errors(), false);
            }
        }

        $user = $request->user();

        $history = ItemHistory::where([
            'User' => $user->User_ID,
            'ItemId' => $request->item_id
        ])->orderBy('CreateTime', 'desc')->take(10)->get();

        return $this->response(200, ['item_history' => $history], __('app.successful'));
    }
}
