<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;
use App\Services\ItemService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * アプリ情報一覧
     */
    public function index(Request $request)
    {
        // 並び替え基準を取得（デフォルトは'id'）
    $sort = $request->input('sort', 'id');
    
    // 並び替え順序を取得（デフォルトは昇順）
    $order = $request->input('order', 'asc');

    // クエリビルダーを使用してアイテムを取得し、並び替えを適用
    $items = Item::with('company')
        ->orderBy($sort, $order)
        ->paginate(5); // ページネーションを使用

    return view('item.index', compact('items'));
 }

    /**
     * アプリ登録
     */
    public function add(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {
            // バリデーション

            $rules = [
                'name' => 'required|max:100',
                'company_id' => 'required_without:company_name',
                'company_name' => 'required_if:company_id,new',
                'stock' => 'required|integer|min:0',
                'comment' => 'nullable|string',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // アイコン画像のバリデーション
            ];


            // 価格オプションが「価格を入力」の場合のバリデーションルールを追加
        if ($request->price_option === 'custom') {
            $rules['price'] = 'required|numeric|min:1'; // 価格は必須で数値、1以上
        }

        // バリデーション実行
        $this->validate($request, $rules);

         // アイコン画像の保存処理
         $iconPath = null;
         if ($request->hasFile('icon')) {
             $iconPath = $request->file('icon')->store('icons', 'public'); // 'public/icons' ディレクトリに保存
         }

        // メーカーが存在するか確認または新規作成
        if ($request->company_id == 'new' || !$company = Company::find($request->company_id)) {
             // `company_id` が `new` または見つからない場合、新しい会社を作成
                $company = Company::create([
                    'company_name' => $request->company_name,
                    'street_address' => '未設定', // 適切なデフォルト値を設定
                    'representative_name' => '未設定', // 適切なデフォルト値を設定
                ]);
            }

            // 価格の決定
            $price = $request->price_option === 'free' ? 0 : ($request->price_option === 'custom' ? $request->price : 0);

            // デバッグ用
// dd([
//     'user_id' => Auth::user()->id,
//     'name' => $request->name,
//     'price' => $price,
//     'stock' => $request->stock,
//     'company_id' => $company->id,  
//     'comment' => $request->comment,
//     'icon' => $iconPath, // ここで$iconPathが正しく設定されているか確認
// ]);

            // アプリ登録
            Item::create([
                'user_id' => Auth::user()->id,
                'name' => $request->name,
                'price' => $price,
                'stock' => $request->stock,
                'company_id' => $company->id,  
                'comment'=> $request->comment,
                'icon' => $iconPath, // アイコン画像のパスを保存
            ]);

            return redirect('/items');
        }

          // GETリクエストのとき
        // 重複する会社名を排除して取得
        $companies = Company::selectRaw('MAX(id) as id, company_name')
            ->groupBy('company_name') // company_nameでグループ化して重複を排除
            ->get();

        return view('item.add', compact('companies'));
    }

    // 編集画面遷移
    public function update(Request $request, ItemService $itemService){
        
        // ルートのidを取得
        $itemId = (int) $request->route('id');

        // アプリ登録したuserのidを取得
        $userId =$request->user()->id;

        if(!$itemService->checkOwnItem($userId, $itemId)){
            throw new AccessDeniedHttpException();
        }

        // $item = Item::where('id',$request->id)->firstOrfail();

        // 編集するアイテムを取得
        $item = Item::where('id', $itemId)->firstOrFail();

        // 重複する会社名を排除して取得
        $companies = Company::selectRaw('MAX(id) as id, company_name')
            ->groupBy('company_name') // company_nameでグループ化して重複を排除
            ->get();


        return view('item.update')->with([
            'item' => $item,
            'companies' => $companies, //会社一覧をビューに渡す
        ]);
    }

    // 編集ボタンで更新処理
    public function updateItem(Request $request){

        $request->validate([
            'name' => 'required|string|max:100',
            'company_id' => [
            'nullable',
            'string',
            function ($attribute, $value, $fail) {
                if ($value !== 'change' && !ctype_digit($value)) {
                    $fail('The company id field must be an integer.');
                }
            }
        ],
            'company_name' => 'nullable|string|max:255', // 新しい会社名が入力されている場合
            'price' => 'nullable|numeric|min:0', // 価格は数値で、最小値は0
            'stock' => 'required|integer|min:0',
            'comment' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // アイコン画像のバリデーション
        ]);
    

        $item = Item::findOrFail($request->id);
        $oldCompanyId = $item->company_id;

        // アイコン画像の保存処理
    if ($request->hasFile('icon')) {

        $file = $request->file('icon');
        // ファイル名を取得
        $filename = $file->getClientOriginalName();
        // デバッグ: ファイルが選択されているか確認

        // dd($filename);

        // 既存のアイコン画像を削除
        if ($item->icon) {
            Storage::disk('public')->delete($item->icon);
        }
        $item->icon = $file->store('icons', 'public'); // 'public/icons' ディレクトリに保存
    }

        // アイテムのフィールドを更新
        $item->name = $request->name;
        $item->company_id = $request->company_id === 'change' ? $this->handleCompany($request) : (int)$request->company_id;
        
        // 価格の設定
        $item->price = isset($request->price_option) && $request->price_option === 'free' 
                 ? 0 
                 : (isset($request->price_option) && $request->price_option === 'custom' 
                    ? $request->price 
                    : $item->price);
        $item->stock = $request->stock;
        $item->comment = $request->comment;

        $item->save();

        // もし古い会社が他のアイテムで使用されていないなら削除
        if ($oldCompanyId !== $item->company_id) {
            $this->deleteCompanyIfUnused($oldCompanyId);
        }
        
        return redirect('/items')->with('success', 'アイテムが更新されました');
    }


    // 古い会社が他のアイテムで使用されていないなら削除
    protected function deleteCompanyIfUnused($companyId)
    {
        $companyInUse = Item::where('company_id', $companyId)->exists();
            if (!$companyInUse) {
            Company::find($companyId)?->delete();
        }
    } 

    
    protected function handleCompany(Request $request)
    {
        if ($request->company_id === 'change') {
            $request->validate([
                'company_name' => 'required|string|max:255',
            ]);

            // 新しい会社を作成
            $company = Company::create([
                'company_name' => $request->company_name,
                'street_address' => '未設定',
                'representative_name' => '未設定',
            ]);

            return $company->id;
        }

        return (int)$request->company_id;
    }

    // 削除処理
    public function delete($id){

        // まず、指定された id のアイテムを削除します
        $item = Item::findOrFail($id);
        $companyId = $item->company_id;

        $item->delete();

        // 他に使用しているアイテムがない場合、会社を削除
        $this->deleteCompanyIfUnused($companyId);

        return redirect('/items');
    }

    // 検索処理
    public function search(Request $request){

         // バリデーションルールの定義
    $request->validate([
        'upper' => 'nullable|integer|min:0',  // 価格の上限は0以上の整数
        'lower' => 'nullable|integer|min:0|lte:upper',  // 価格の下限は0以上の整数かつ上限以下
        'download_upper' => 'nullable|integer|min:0',  // ダウンロード数の上限は0以上の整数
        'download_lower' => 'nullable|integer|min:0|lte:download_upper',  // ダウンロード数の下限は0以上の整数かつ上限以下
        'keyword' => 'nullable|string|max:255',  // キーワード検索は任意の文字列
    ], [
        'lower.lte' => '価格の下限は上限値以下である必要があります。',
        'download_lower.lte' => 'ダウンロード数の下限は上限値以下である必要があります。',
    ]);

        $keyword = $request->input('keyword'); //キーワード
        $upperPrice = $request->input('upper'); // 価格の上限
        $lowerPrice = $request->input('lower'); // 価格の下限
        $downloadUpper = $request->input('download_upper'); // ダウンロード数/万の上限
        $downloadLower = $request->input('download_lower'); // ダウンロード数/万の下限

        $items = Item::query();

        // キーワード検索
    if (!empty($keyword)) {
        $items->where(function($query) use ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%")
                  ->orWhere('price', 'LIKE', "%{$keyword}%")
                  ->orWhere('stock', 'LIKE', "%{$keyword}%")
                  ->orWhere('comment', 'LIKE', "%{$keyword}%")
                  ->orWhereHas('company', function($query) use ($keyword) {
                      $query->where('company_name', 'LIKE', "%{$keyword}%");
                  });
        });
    }

        /* 価格範囲検索 */
        if (!empty($upperPrice)) {
            $items->where('price', '<=', $upperPrice);
        }

        if (!empty($lowerPrice)) {
            $items->where('price', '>=', $lowerPrice);
        }

        /* ダウンロード数/万範囲検索 */
        if (!empty($downloadUpper)) {
            $items->where('stock', '<=', $downloadUpper);
        }

        if (!empty($downloadLower)) {
            $items->where('stock', '>=', $downloadLower);
        }


    /* ページネーション */
    // 5レコードずつ表示する
    $items = $items->paginate(5);

        return view('item.search',compact('items', 'keyword'));
    }
}
