<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    // ユーザー一覧表示
    public function index()
    {
        // ユーザー一覧を10件ずつページネートする
        $users = User::select('id', 'name', 'email', 'auth', 'created_at', 'updated_at')->paginate(3);
        return view('user.index', compact('users'));
    }

    // ユーザー編集画面表示
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('user.edit', compact('user'));
    }

    // ユーザー更新処理
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        
        // チェックボックスが送信されていない場合は一般ユーザーに設定
        $user->auth = $request->has('auth') ? 1 : 0;

        // パスワードの変更は必要な場合のみ
        if ($request->input('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'ユーザー情報が更新されました');
    }

    // ユーザー削除処理
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('success', 'ユーザーが削除されました');
    }

    // 複数削除処理
    public function bulkDelete(Request $request){
        // リクエストから user_ids を取得
        $userIds = $request->input('user_ids');

        if (is_array($userIds) && !empty($userIds)) {
            // ユーザーを削除
            User::whereIn('id', $userIds)->delete();
            return response()->json(['success' => 'ユーザーが削除されました。']);
        } else {
            return response()->json(['error' => 'ユーザーIDが見つかりません。'], 400);
        }
    }

    // 検索処理
    public function UserSearch(Request $request){

        // バリデーションルールの定義
   $request->validate([
       'userName' => 'nullable|string|max:255',  // キーワード検索は任意の文字列
   ]);

       $userName = $request->input('userName'); //ユーザー名
       $users = User::query();

       // ユーザー名検索
   if (!empty($userName)) {
       $users->where(function($query) use ($userName) {
           $query->where('name', 'LIKE', "%{$userName}%");
       });
   }
   /* ページネーション */
   // 5レコードずつ表示する
   $users = $users->paginate(3);

       return view('user.index',compact('users', 'userName'));
   }

   public function clearSelection(Request $request) {
    if ($request->input('clearSelection')) {
        Session::forget('user_ids'); // 選択状態をリセット
        return response()->json(['status' => 'success']);
    }
    return response()->json(['status' => 'failed'], 400);
}
}
