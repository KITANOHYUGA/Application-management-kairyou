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
use Illuminate\Pagination\LengthAwarePaginator; // ここで正しくインポート
use Intervention\Image\Facades\Image; // Intervention Imageを利用


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
        
    // セッションからソート基準と並び替え順序を取得（デフォルトは 'id', 'asc'）
    $sort = $request->session()->get('sort', 'id');
    $order = $request->session()->get('order', 'asc');
    $type = $request->session()->get('filter_type', null);

    // 「一覧画面に戻る」リンクを押した場合、セッションの選択状態をクリア
    if ($request->has('reset')) {
        $request->session()->forget(['selected_items']);
    }

    // 並び替えやフィルタリングが行われた場合、選択状態をクリア
    if ($request->has('sort') || $request->has('order') || $request->has('type')) {
        $request->session()->forget('selected_items'); // ここで選択状態をクリア
    }
    
    if ($request->has('sort')) {
        $sort = $request->input('sort', 'id');  // リクエストからソート基準を取得
        $request->session()->put('sort', $sort); // ソート基準をセッションに保存
    }
    
    if ($request->has('order')) {
        $order = $request->input('order', 'asc');
        $request->session()->put('order', $order);
    }
    
    // リクエストにカテゴリー選択がある場合、セッションに保存
    if ($request->has('type')) {
        $type = $request->input('type');
        $request->session()->put('filter_type', $type);
    }

     // フロントエンドの値をデータベースの値にマッピング
     $typeMapping = [
        'game' => 1,
        'education' => 2,
        'utility' => 3,
        'sports' => 4,
        'rpg' => 5,
        'others' => 6,
    ];


    // クエリビルダーを使用してアイテムを取得し、並び替えを適用
    $items = Item::with('company');
    
    // カテゴリーが選択されている場合はフィルタリング
    if ($type && isset($typeMapping[$type])) {
        $items = $items->where('type', $typeMapping[$type]); // データベースの値に変換してフィルタリング
    }

    // 並び替えを適用（カテゴリー別の場合は特別な処理が不要）
    if ($sort !== 'type') {
        $items = $items->orderBy($sort, $order);
    } else {
        // 例えば、typeでソートする場合には別途処理を追加することも考慮
        $items = $items->orderBy('type', $order);
    }

    // 並び替えを適用
    $items = $items->orderBy($sort, $order);

    // ページネーションを使用
    $items = $items->paginate(5);
    
    // ページネータのインスタンスに 'appends' を適用して、現在のクエリパラメータを保持
    $items->appends($request->query());

    // セッションから選択されたアイテムを取得
    $selectedItems = $request->session()->get('selected_items', []);

    return view('item.index', compact('items', 'selectedItems'));

    // return view('item.index', compact('items'));
 }

 public function reset(Request $request)
{
    // // セッションのフィルターやソートの条件をクリア
    $request->session()->forget(['filter', 'sort', 'order', 'selected_items', 'filter_type']);
    
    // 一覧画面にリダイレクト
    return redirect()->route('items.index')->with('success', session('success'));
}

public function searchReset(Request $request)
{
    // 一覧画面にリダイレクト
    return redirect()->route('items.search');
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
                'type' => 'required|in:1,2,3,4,5,6', // カテゴリー選択のバリデーション
                'company_id' => 'required_without:company_name',
                'company_name' => 'required_if:company_id,new',
                'dawnload' => 'required|integer|min:0',
                'comment' => 'nullable|string',
                'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // アイコン画像のバリデーション
            ];

            // カスタムメッセージ
            $messages = [
                'name.required' => '名前は必須項目です。',
                'name.max' => '名前は100文字以内で入力してください。',
                'type.required' => 'カテゴリーを選択してください。',
                'type.in' => '選択されたカテゴリーが無効です。',
                'company_id.required_without' => '会社IDは必須です（会社名が入力されていない場合）。',
                'company_name.required_if' => '会社IDが「new」の場合は会社名を入力してください。',
                'dawnload.required' => 'ダウンロード数は必須です。',
                'dawnload.integer' => 'ダウンロード数は整数でなければなりません。',
                'dawnload.min' => 'ダウンロード数は0以上でなければなりません。',
                'comment.string' => 'コメントは文字列で入力してください。',
                'icon.image' => 'アイコン画像はjpeg、png、jpg、gifの形式でなければなりません。',
                'icon.mimes' => 'アイコン画像の形式はjpeg、png、jpg、gifでなければなりません。',
                'icon.max' => 'アイコン画像のサイズは2MB以下でなければなりません。',
            ];

            // フラッシュメッセージをセッションに保存
            session()->flash('success', '登録が完了しました');


        // 価格オプションが「価格を入力」の場合のバリデーションルールを追加
        if ($request->price_option === 'custom') {
            $rules['price'] = 'required|numeric|min:1'; // 価格は必須で数値、1以上
        }

        // バリデーション実行
        // $this->validate($request, $rules);

        // バリデーションの実行
        $validated = $request->validate($rules, $messages);

        //  // アイコン画像の保存処理
         $iconPath = null;
         if ($request->hasFile('icon')) {
             $iconPath = $request->file('icon')->store('icons', 'public'); // 'public/icons' ディレクトリに保存
         }

            // アイコン画像の保存処理
    // $iconPath = null;
    // if ($request->hasFile('icon')) {
    //     $file = $request->file('icon');
        
    //     // 画像のリサイズ（例: 横200px、縦200pxにリサイズ）
    //     $resizedImage = Image::make($file)->resize(200, 200, function ($constraint) {
    //         $constraint->aspectRatio();  // アスペクト比を維持
    //         $constraint->upsize();       // 元画像より大きくしない
    //     });

    //     // 画像を指定したディレクトリに保存
    //     $iconPath = 'icons/' . time() . '_' . $file->getClientOriginalName();
    //     $resizedImage->save(storage_path('app/public/' . $iconPath), 100);
    // }

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
//     'dawnload' => $request->dawnload,
//     'company_id' => $company->id,  
//     'comment' => $request->comment,
//     'icon' => $iconPath, // ここで$iconPathが正しく設定されているか確認
// ]);

            // アプリ登録
            Item::create([
                'user_id' => Auth::user()->id,
                'name' => $request->name,
                'type' => $request->type, // カテゴリーを保存
                'price' => $price,
                'dawnload' => $request->dawnload,
                'company_id' => $company->id,  
                'comment'=> $request->comment,
                'icon' => $iconPath, // アイコン画像のパスを保存
            ]);

            return redirect()->route('items.reset')->with('success', '登録が完了しました');
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

        // $request->validate([
            $rules = [
            'name' => 'required|string|max:100',
            'type' => 'required|integer|between:1,6', // type フィールドのバリデーションを追加
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
            'dawnload' => 'required|integer|min:0',
            'comment' => 'nullable|string',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // アイコン画像のバリデーション
        ];

        // カスタムバリデーションメッセージ
        $messages = [
            'name.required' => 'ゲーム名は必須項目です。',
            'name.string' => 'ゲーム名は文字列でなければなりません。',
            'name.max' => 'ゲーム名は100文字以内で入力してください。',
            'type.required' => 'カテゴリーを選択してください。',
            'type.integer' => 'カテゴリーの値が無効です。',
            'type.between' => 'カテゴリーは1から6の間で選択してください。',
            'company_id.integer' => '会社IDは整数でなければなりません。',
            'company_name.max' => '会社名は255文字以内で入力してください。',
            'price.numeric' => '価格は数値で入力してください。',
            'price.min' => '価格は0以上でなければなりません。',
            'dawnload.required' => 'ダウンロード数は必須です。',
            'dawnload.integer' => 'ダウンロード数は整数で入力してください。',
            'icon.image' => 'アイコンは画像ファイルでなければなりません。',
            'icon.mimes' => 'アイコンはjpeg、png、jpg、gif形式のファイルでなければなりません。',
            'icon.max' => 'アイコンのサイズは2048KB以内でなければなりません。',
        ];

        // バリデーションの実行
        $request->validate($rules, $messages);
    

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
        $item->dawnload = $request->dawnload;
        $item->comment = $request->comment;
        // カテゴリ（type）の更新
        $item->type = $request->type;

        $item->save();

        // もし古い会社が他のアイテムで使用されていないなら削除
        if ($oldCompanyId !== $item->company_id) {
            $this->deleteCompanyIfUnused($oldCompanyId);
        }
        
        return redirect('/items')->with('success', 'アプリ情報が更新されました');
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

        return redirect('/items/reset');
    }

//     public function delete(Request $request)
// {
//     // セッションから選択されたアイテムを取得
//     $selectedItems = $request->session()->get('selected_items', []);

//     if (!empty($selectedItems) && count($selectedItems) > 0) {
//         // 選択されたアイテムの会社IDを取得
//         $companyIds = Item::whereIn('id', $selectedItems)->pluck('company_id')->unique();

//         // dd($selectedItems);
        
//         // 選択されたアイテムを削除
//         Item::whereIn('id', $selectedItems)->delete();

//         // 他に使用しているアイテムがない場合、会社を削除
//         foreach ($companyIds as $companyId) {
//             $this->deleteCompanyIfUnused($companyId);
//         }

//         // セッションの選択状態をクリア
//         $request->session()->forget('selected_items');
        
//         return redirect()->route('items.reset')->with('success', '選択されたアイテムを削除しました');
//     }

//     return redirect()->route('items.reset')->with('error', '削除するアイテムが選択されていません');
// }

public function deleteSelected(Request $request)
{
    // // JSONの確認（リクエストが正しく来ているか）
    // if (!$request->ajax() || !$request->has('selectedItems')) {
    //     return response()->json(['error' => '不正なリクエストです'], 400); // 400 Bad Request
    // }

    $selectedItems = $request->input('selectedItems', []);

    if (!$selectedItems || !is_array($selectedItems) || count($selectedItems) === 0) {
        return response()->json(['error' => '削除するアイテムが選択されていません'], 400);
    }


    if (empty($selectedItems)) {
        return response()->json(['error' => '削除するアイテムが選択されていません'], 400);
    }

    $companyIds = Item::whereIn('id', $selectedItems)->pluck('company_id')->unique();

    // アイテム削除
    Item::whereIn('id', $selectedItems)->delete();

    // 使用していない会社を削除
    foreach ($companyIds as $companyId) {
        $this->deleteCompanyIfUnused($companyId);
    }

    return response()->json(['message' => '選択されたアイテムが削除されました'], 200); // 正常なレスポンス
}



    // 検索処理
    public function search(Request $request){

         // バリデーションルールの定義
    $request->validate([
        'upper' => 'nullable|integer|min:0',  // 価格の上限は0以上の整数
        'lower' => 'nullable|integer|min:0|lte:upper',  // 価格の下限は0以上の整数かつ上限以下
        'dawnload_upper' => 'nullable|integer|min:0',  // ダウンロード数の上限は0以上の整数
        'dawnload_lower' => 'nullable|integer|min:0|lte:dawnload_upper',  // ダウンロード数の下限は0以上の整数かつ上限以下
        'keyword' => 'nullable|string|max:255',  // キーワード検索は任意の文字列
    ], [
        'lower.lte' => '価格の下限は上限値以下である必要があります。',
        'dawnload_lower.lte' => 'ダウンロード数の下限は上限値以下である必要があります。',
    ]);

        $keyword = $request->input('keyword'); //キーワード
        $upperPrice = $request->input('upper'); // 価格の上限
        $lowerPrice = $request->input('lower'); // 価格の下限
        $dawnloadUpper = $request->input('dawnload_upper'); // ダウンロード数/万の上限
        $dawnloadLower = $request->input('dawnload_lower'); // ダウンロード数/万の下限

        $items = Item::query();

        // キーワード検索
    if (!empty($keyword)) {
        $items->where(function($query) use ($keyword) {
            $query->where('name', 'LIKE', "%{$keyword}%")
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
        if (!empty($dawnloadUpper)) {
            $items->where('dawnload', '<=', $dawnloadUpper);
        }

        if (!empty($dawnloadLower)) {
            $items->where('dawnload', '>=', $dawnloadLower);
        }


    /* ページネーション */
    // 5レコードずつ表示する
    $items = $items->paginate(5);

        return view('item.search',compact('items', 'keyword'));
    }

    public function saveSelected(Request $request)
{
    // 新しく選択されたアイテムとチェックを外したアイテム
    $newSelectedItems = $request->input('selected_items', []);
    $unselectedItems = $request->input('unselected_items', []);

    // セッションに保存されている選択状態を取得
    $selectedItems = $request->session()->get('selected_items', []);

    // 既存の選択状態からチェックを外したアイテムを除外
    $updatedSelectedItems = array_diff($selectedItems, $unselectedItems);

    // 更新された選択状態に新しい選択を追加
    $mergedSelectedItems = array_unique(array_merge($updatedSelectedItems, $newSelectedItems));

    // マージされた選択状態をセッションに保存
    $request->session()->put('selected_items', $mergedSelectedItems);

    return response()->json(['message' => '選択状態が保存されました']);
}

public function getSelected(Request $request)
{
    // セッションから選択されたアイテムを取得
    $selectedItems = $request->session()->get('selected_items', []);

    // 配列であるか確認のためのログ
    \Log::info('Selected Items:', $selectedItems);

    return response()->json(['selected_items' => $selectedItems]);
}

public function clearSelection()
{

    // ログを出力してセッションがクリアされたか確認
    \Log::info('選択されたアイテムがクリアされました');

    // セッションに保存されたチェックボックスの選択状態をクリア
    session()->forget('selected_items');

    // カテゴリー選択のセッションをクリア
    session()->forget('filter_type');

    return response()->json(['success' => true]);
}

public function showSelected(Request $request)
{

    // フォームから送信された選択アイテムのIDを取得
    $selectedItems = json_decode($request->input('selected_items', '[]'), true);

    if (!empty($selectedItems)) {
        // 選択されたIDに基づいてアイテムを取得
        $items = Item::whereIn('id', $selectedItems)->paginate(5);
    } else {
        // 選択されていない場合は空のLengthAwarePaginatorを作成
        $items = new LengthAwarePaginator([], 0, 5, 1, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    return view('item.index', compact('items','selectedItems')); // 選択されたアイテムを表示するビュー
}

 // 選択されたアイテムをセッションに保存
 public function saveSelectedItems(Request $request)
 {
     $selectedItems = $request->input('selectedItems', []);
     // セッションに選択されたアイテムを保存
     $request->session()->put('selected_items', $selectedItems);
     
     return response()->json(['status' => 'success']);
 }


}
