@extends('adminlte::page')

@section('title', 'アプリ情報一覧')

@section('content_header')
<!-- 並び替えが行われていない場合にのみ表示 -->
    @if(!request('sort') && !request('order'))
        <h1>アプリ情報一覧</h1>
    @endif
@stop

@section('content')
<div class="row">

    <div class="col-12">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- 並び替えフォーム -->
    <form action="{{ url('items') }}" method="GET" id="sortForm" class="form-inline">
        <div class="form-group">
            <!-- 並び替え基準の選択 -->
            <select name="sort" id="sort" class="form-control mr-1" style="padding: 2px;">
                <option value="" disabled selected>並び替え選択</option>
                <option value="dawnload" {{ request('sort') == 'dawnload' ? 'selected' : '' }}>ダウンロード数</option>
                <option value="price" {{ request('sort') == 'price' ? 'selected' : '' }}>価格</option>
                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>名前</option>
            </select>
        </div>


        <!-- 並び替え順序の選択 -->
        <div id="order_group" class="form-group ml-1">
            <select name="order" id="order" class="form-control" style="padding: 2px;">
                <option value="" disabled selected>↓ or ↑</option>
                <option value="asc" {{ request('order') == 'asc' ? 'selected' : '' }}>昇順</option>
                <option value="desc" {{ request('order') == 'desc' ? 'selected' : '' }}>降順</option>
            </select>
        </div>

        <button type="button" id="clearButton" class="btn btn-outline-secondary ml-1">クリア</button>

        <!-- カテゴリーを選択するドロップダウンメニュー -->
        <div id="type_group" class="form-group ml-auto">
            <select name="type" id="type" class="form-control" style="padding: 2px;">
                <option value="" disabled selected>カテゴリー別</option>
                <option value="game" {{ request('type') == 'game' ? 'selected' : '' }}>ゲーム</option>
                <option value="education" {{ request('type') == 'education' ? 'selected' : '' }}>教育</option>
                <option value="utility" {{ request('type') == 'utility' ? 'selected' : '' }}>ユーティリティ</option>
                <option value="sports" {{ request('type') == 'sports' ? 'selected' : '' }}>スポーツ</option>
                <option value="rpg" {{ request('type') == 'rpg' ? 'selected' : '' }}>ロールプレイング</option>
                <option value="others" {{ request('type') == 'others' ? 'selected' : '' }}>その他</option>
            </select>
        </div>

    </form>

    <!-- ポップアップ表示の部分 -->
    @if (session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    alert("{{ session('success') }}");
                });
            </script>
        @endif

        <div class="card mt-5">
            <div class="card-body table-responsive p-0">
            <form action="{{ url('/items/delete-selected') }}" method="POST" id="deleteForm" onsubmit="return confirmDelete()" data-pjax>
            @csrf
            @method('DELETE')
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            @if(auth()->user()->auth == 1 && $items->isNotEmpty())
                                <th><input type="checkbox" id="selectAll" @if (request()->is('items/show-selected')) style="display:none;" @endif></th>
                            @endif
                            <th style="visibility: hidden;">アプリ画像</th>
                            <th>アプリ名</th>
                            <th>カテゴリー</th>
                            <th>会社名</th>
                            <th>価格/円</th>
                            <th>ダウンロード数/万</th>
                            <th>コメント</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                        <tr>
                            @if(auth()->user()->auth == 1)
                            <td class="align-middle">
                                <input type="checkbox" name="selected_items[]" value="{{ $item->id }}" class="item-checkbox" {{ in_array($item->id, $selectedItems) ? 'checked' : '' }}>
                            </td>
                            @endif
                            <td class="align-middle">
                                @if ($item->icon)
                                <img src="{{ asset('storage/' . $item->icon) }}" class="rounded-circle" style="width: 70px; height: 70px; object-fit: cover; border: 1px solid #ccc; padding: 2px; margin: 2px;">
                                @else
                                <img src="{{ asset('storage/icons/default.png') }}" class="rounded-circle" style="width: 70px; height: 70px; object-fit: cover; border: 1px solid #ccc; padding: 2px; margin: 2px;">
                                @endif
                            </td>
                            <td class="align-middle">{{ $item->name }}</td>
                            <td class="align-middle">
                            @switch($item->type)
                                @case(1)
                                    ゲーム
                                    @break
                                @case(2)
                                    教育
                                    @break
                                @case(3)
                                    ユーティリティー
                                    @break
                                @case(4)
                                    スポーツ
                                    @break
                                @case(5)
                                    ロールプレイング
                                    @break
                                @case(6)
                                    その他
                                    @break
                                @default
                                    未分類
                            @endswitch
                            </td>
                            <td class="align-middle">{{ $item->company->company_name }}</td>
                            <td class="align-middle">{{ $item->price == 0 ? '無料' : $item->price }}</td>
                            <td class="align-middle">{{ $item->dawnload }}</td>
                            <td class="align-middle">{{ $item->comment }}</td>
                            <td class="align-middle">
                            @if(auth()->user()->auth == 1)
                            <div>
                                <a href="{{ url('items/update/'.$item->id) }}" class="btn btn-outline-primary btn-sm">編集</a>
                            </div>
                            @endif
                            </td>
                           
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9">登録されたアプリ情報がありません</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(auth()->user()->auth == 1)
           <!-- 削除ボタン -->
            <div class="mb-3 d-flex">
                <button type="submit" class="btn btn-danger" id="bulkDeleteBtn">削除</button>
        @endif
        </form>

    <form action="{{ url('/items/show-selected') }}" method="POST" id="showSelectedForm" class="ml-auto">
        @csrf
        <button type="submit" id="showSelectedButton" class="btn btn-primary" style="display: none;">選択している項目を表示</button>
    </form>
        </div>
    </div>
</div>
</div>
<div class="d-flex justify-content-center">
{{ $items->appends(['selected_items' => json_encode($selectedItems)])->links('pagination::bootstrap-4') }}

</div>
</div>
@stop

@section('css')
@stop

@section('js')
<script>

// 並び替え基準が選択された時の処理
document.getElementById('sort').addEventListener('change', function() {
    const orderGroup = document.getElementById('order_group');

    // 昇順降順選択を表示またはリセット
    orderGroup.style.display = 'block';
    document.getElementById('order').value = '';

    // 並べ替えが選択された時に選択状態をクリア
    clearAllSelectionStates(); // localStorage の選択状態を完全にクリア

    clearCheckboxSelection();  // チェックボックスの選択状態をリセット
    sessionStorage.setItem('disableRestore', 'true'); // 状態復元を無効にするフラグを立てる

    // フォームを送信してリロード
    document.getElementById('sortForm').submit();
});

document.getElementById('sortForm').addEventListener('submit', function(event) {
    clearCheckboxSelection();  // 送信前にチェックボックスの選択をリセット
});


// 並び替え順序の選択時に自動的にフォームを送信
document.getElementById('order').addEventListener('change', function() {
    if (this.value && document.getElementById('sort').value) {
        // 並べ替え順序選択時にも選択状態をクリア
        clearAllSelectionStates(); // localStorage の選択状態を完全にクリア
        clearCheckboxSelection();  // チェックボックスの選択状態をリセット
        sessionStorage.setItem('disableRestore', 'true'); // フラグを立てる
        
        document.getElementById('sortForm').submit();
    }
});

// カテゴリーが選択された時に自動的にフォームを送信
document.getElementById('type').addEventListener('change', function() {
    if (this.value) {
        // カテゴリー選択時にも選択状態をクリア
        clearAllSelectionStates(); // localStorage の選択状態を完全にクリア
    
        console.log('After clearing state:');
console.log('localStorage:', localStorage);
console.log('sessionStorage:', sessionStorage);

        clearCheckboxSelection();  // チェックボックスの選択状態をリセット
        sessionStorage.setItem('disableRestore', 'true'); // フラグを立てる

        document.getElementById('sortForm').submit();
    }
});


// チェックボックスの選択状態をクリアする関数
function clearCheckboxSelection() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false; // 全てのチェックボックスの選択を解除
    });

    // 全選択用のチェックボックスもリセット
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
    }
}

function clearSelectionState() {
    // 選択状態をすべてクリア
    localStorage.removeItem('selected_items');
    localStorage.removeItem('unchecked_items_page');
    localStorage.removeItem('select_all_checked_page');

    // すべてのページごとの選択状態をリセット
    for (let i = 1; i <= localStorage.length; i++) {
        localStorage.removeItem(`selected_items_page_${i}`);
        localStorage.removeItem(`unchecked_items_page_${i}`);
        localStorage.removeItem(`select_all_checked_page_${i}`);
    }
}


// ページごとの選択状態をクリアする関数
function clearAllSelectionStates() {
    const keys = Object.keys(localStorage);
    keys.forEach(key => {
        if (key.startsWith('selected_items_page_') || key.startsWith('select_all_checked_page_')) {
            localStorage.removeItem(key); // localStorage から選択状態を完全に削除
        }
    });
     // グローバルな selected_items や select_all_checked の削除も追加
     localStorage.removeItem('selected_items');
    localStorage.removeItem('select_all_checked');

    sessionStorage.removeItem('selected_items'); // セッションの選択状態もリセット
    sessionStorage.removeItem('disableRestore'); // フラグをクリア
}

// 初期化
document.addEventListener('DOMContentLoaded', function () {
    console.log('Before form submission (type change):');
        console.log('localStorage:', localStorage);
        console.log('sessionStorage:', sessionStorage);

        Object.keys(localStorage).forEach(key => {
    if (key.startsWith('unchecked_items_page_')) {
        localStorage.removeItem(key);
    }
});

    // ページが読み込まれた時に状態を復元
    restoreState();
    updateButtonStates();

    // フォームが送信される際にチェックボックスの選択状態をクリアする
    document.getElementById('sortForm').addEventListener('submit', function(event) {
        clearCheckboxSelection();  // 送信前にチェックボックスの選択をリセット
    });
});

document.addEventListener('DOMContentLoaded', function () {

    // AdminLTEメニューの「アプリ情報一覧」リンクのクリックイベントリスナー
    // const resetMenuLink = document.querySelector('a[href$="items/reset"]');
    // if (resetMenuLink) {
    //     resetMenuLink.addEventListener('click', function (event) {
    //         // ページの遷移を一旦止める
    //         event.preventDefault();

    //         // クライアント側の選択状態をクリアする
    //         clearAllSelectionStates(); // localStorage の選択状態を完全にクリア

    //         console.log('After clearing state:');
    //         console.log('localStorage:', localStorage);
    //         console.log('sessionStorage:', sessionStorage);

    //         clearCheckboxSelection();  // チェックボックスの選択状態をリセット
    //         sessionStorage.removeItem('disableRestore'); // 状態復元を無効にするためのフラグをクリア

    //         // 状態クリア後にリダイレクトを行う
    //         setTimeout(() => {
    //             // リンク先にリダイレクト
    //             window.location.href = resetMenuLink.href;
    //         }, 100); // 状態クリア処理を確実に終わらせるために少し遅延
    //     });
    // }
     // AdminLTEのメニューリンクを全て取得
     const menuLinks = document.querySelectorAll('.nav-link'); // AdminLTEで生成されるメニューリンクを取得
    
    // デバッグ用：取得したリンクの確認
    console.log('取得したメニューリンク:', menuLinks);

    // 各メニューリンクにクリックイベントを追加
    menuLinks.forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault(); // ページ遷移を一旦止める
            
            // クリックされたリンクをデバッグログに出力
            console.log('クリックされたリンク:', link);

            // 選択状態をクリアする関数を呼び出し
            clearAllSelectionStates();  // ページ遷移前に選択状態をクリア
            clearCheckboxSelection();   // チェックボックスの選択状態もリセット
            sessionStorage.removeItem('disableRestore'); // 状態復元を無効にするためのフラグをクリア

            // リンクに `onclick` 属性が指定されていれば、それを実行
            const onclickAttr = link.getAttribute('onclick');
            if (onclickAttr) {
                eval(onclickAttr);  // `onclick` 属性の内容を評価して実行
            }

            // 遅延を設けてページ遷移
            setTimeout(() => {
                const href = link.getAttribute('href');  // リンクのhref属性を取得
                console.log('リダイレクト先のURL:', href);  // リダイレクト先を確認
                window.location.href = href;  // ページ遷移
            }, 100);  // 100ms遅延してからリダイレクト
        });
    });
});


// ページが読み込まれた時の処理
window.onload = function () {
    const sortValue = document.getElementById('sort').value;
    const orderGroup = document.getElementById('order_group');

    // 並び替え基準が設定されていれば昇順降順を表示
    orderGroup.style.display = sortValue ? 'block' : 'none';
};

// ページの状態を復元する関数
function restoreState() {
    const urlParams = new URLSearchParams(window.location.search);
    
    // 並べ替えやカテゴリ変更時は選択状態を復元しない
    if (sessionStorage.getItem('disableRestore') === 'true' || 
        urlParams.has('sort') || 
        urlParams.has('order') || 
        urlParams.has('type')
    ) {
        console.log('Skipping state restore due to sorting/filtering.');
        // チェックボックスの選択をリセット
        clearCheckboxSelection();
        sessionStorage.removeItem('disableRestore'); // フラグをクリアして次の操作に影響がないようにする
        return;  // 状態を復元しない
    }

    // 復元フラグが立っていない場合、選択状態を復元する処理を続ける
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const selectedItems = getSelectedItems();
    const isSelectAllChecked = getSelectAllState(); // ここで定義

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectedItems.includes(checkbox.value) || isSelectAllChecked;
    });

    updateButtonStates();
}

// ボタンの状態を更新する関数
function updateButtonStates() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    const deleteButton = document.getElementById('bulkDeleteBtn');
    const showSelectedButton = document.getElementById('showSelectedButton');

    const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked); // 1つでもチェックされているか
    if (deleteButton) {
        deleteButton.style.display = anyChecked ? 'block' : 'none';
        deleteButton.disabled = !anyChecked;
    }
    if (showSelectedButton) {
        showSelectedButton.style.display = anyChecked ? 'block' : 'none';
        showSelectedButton.disabled = !anyChecked;
    }
}

// 全選択の状態を取得する関数
function getSelectAllState() {
    const currentPage = getCurrentPageNumber();
    return localStorage.getItem(`select_all_checked_page_${currentPage}`) === 'true';
}

// 選択されたアイテムを取得する関数
function getSelectedItems() {
    const currentPage = getCurrentPageNumber();
    return JSON.parse(localStorage.getItem(`selected_items_page_${currentPage}`)) || [];
}


// 現在のページ番号を取得する関数
function getCurrentPageNumber() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('page') || '1'; // ページ番号がない場合は1とする
}

// ページネーション後のイベントリスナー
document.addEventListener('pjax:complete', function() {
    restoreState(); // ページネーション時に状態を復元
    updateButtonStates();
    clearSelectionState();
});



clearButton.addEventListener('click', function () {
    // サーバー側で選択状態をクリアするリクエストを送信
    fetch("{{ route('items.clearSelection') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            throw new Error('Network response was not ok');
        }
    })
    .then(data => {
        if (data.success) {
            console.log("クリア成功");

            // カテゴリー選択のリセット (フォームが存在する場合)
            const typeSelect = document.getElementById('type');
            if (typeSelect) {
                typeSelect.selectedIndex = 0;
            }

            // チェックボックスの選択を全て解除
            const checkboxes = document.querySelectorAll('.item-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });

            // クライアント側の選択データを全てクリア
            localStorage.removeItem('selected_items');

            // ページごとの選択状態をクリア
            clearAllSelectionStates();

            // selectAll チェックボックスもリセット
            const selectAllCheckbox = document.getElementById('selectAll');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
            }

            // ボタンの状態を更新
            updateButtonStates();

            // URLからクエリパラメータを削除しつつリロードすることで初期状態のページに戻る
            const url = new URL(window.location.href);
            url.search = ''; // クエリパラメータをクリア
            window.history.pushState({}, '', url); // クエリパラメータを削除したURLを更新

            // ページをリロードして初期状態の一覧に戻る
            window.location.replace("{{ url('items/reset') }}");
        } else {
            console.error('Failed to clear selection');
        }
    })
    .catch(error => console.error('Error:', error));
});

document.addEventListener('DOMContentLoaded', function () {
    const deleteButton = document.getElementById('bulkDeleteBtn');
    const selectAllCheckbox = document.getElementById('selectAll');
    const showSelectedButton = document.getElementById('showSelectedButton');
    const checkboxes = document.querySelectorAll('.item-checkbox');

    // ページ番号を取得する関数
    function getCurrentPageNumber() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('page') || '1'; // ページ番号がない場合は1
    }

    const currentPage = getCurrentPageNumber();

    // 不要なunchecked_items_page_Xデータを削除
    Object.keys(localStorage).forEach(key => {
        if (key.startsWith('unchecked_items_page_')) {
            localStorage.removeItem(key);
        }
    });


    // 全選択の状態を保存
    function saveSelectAllState(isChecked) {
        localStorage.setItem(`select_all_checked_page_${currentPage}`, isChecked ? 'true' : 'false');
    }

    function getSelectAllState(page) {
        return localStorage.getItem(`select_all_checked_page_${page}`) === 'true';
    }

    // 選択された項目を保存
    function saveSelectedItems() {
        const selectedItems = Array.from(checkboxes)
            .filter(checkbox => checkbox.checked)
            .map(checkbox => checkbox.value);
        localStorage.setItem(`selected_items_page_${currentPage}`, JSON.stringify(selectedItems));
    }

    function getSelectedItemsForPage(page) {
        return JSON.parse(localStorage.getItem(`selected_items_page_${page}`)) || [];
    }

    // チェック解除された項目を保存
    function saveUncheckedItems() {
        const uncheckedItems = Array.from(checkboxes)
            .filter(checkbox => !checkbox.checked)
            .map(checkbox => checkbox.value);
        localStorage.setItem(`unchecked_items_page_${currentPage}`, JSON.stringify(uncheckedItems));
    }

    function getUncheckedItemsForPage(page) {
        return JSON.parse(localStorage.getItem(`unchecked_items_page_${page}`)) || [];
    }

    // すべてのページの選択されたアイテムを集約
    function getAllSelectedItems() {
        const allSelectedItems = [];
        // for (let i = 1; i <= localStorage.length; i++) {
        //     const pageItems = getSelectedItemsForPage(i);
        //     const uncheckedItems = getUncheckedItemsForPage(i);
        //     // 未選択アイテムを除外
        //     const filteredPageItems = pageItems.filter(item => !uncheckedItems.includes(item));
        //     allSelectedItems.push(...filteredPageItems);
        // }
        const pages = Object.keys(localStorage).filter(key => key.startsWith('selected_items_page_'));
        
        pages.forEach(pageKey => {
            const pageItems = JSON.parse(localStorage.getItem(pageKey)) || [];
            allSelectedItems.push(...pageItems);
        });
        return allSelectedItems;
    }

    // 全選択ボタンのイベントハンドラ
    if (selectAllCheckbox) {  // ここで要素が存在するか確認
    selectAllCheckbox.addEventListener('change', function () {
        const isChecked = this.checked;
        saveSelectAllState(isChecked);
        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
        });

        if (isChecked) {
            const allItems = Array.from(checkboxes).map(checkbox => checkbox.value);
            localStorage.setItem(`selected_items_page_${currentPage}`, JSON.stringify(allItems));
            localStorage.removeItem(`unchecked_items_page_${currentPage}`); // 全選択時に除外リストをクリア
        } else {
            saveUncheckedItems(); // 全選択解除時、除外アイテムを保存
        }
        updateButtonStates();
    });
}

    // 全選択チェックボックスの状態を更新
    function updateSelectAllCheckbox() {
        const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
        if (selectAllCheckbox) {  // ここで要素が存在するか確認
        selectAllCheckbox.checked = allChecked;
        saveSelectAllState(allChecked);
    }
}

    // チェックボックスのイベントリスナー
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            saveSelectedItems();
            saveUncheckedItems(); // チェック解除したアイテムを保存
            updateSelectAllCheckbox(); // 全選択の状態を更新
            updateButtonStates();
        });
    });

    // ボタンの状態を更新
    function updateButtonStates() {
        // const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
        // if (deleteButton) {  // ここで要素が存在するか確認
        //     deleteButton.style.display = anyChecked ? 'block' : 'none';
        //     deleteButton.disabled = !anyChecked;
        // }
        const selectedItems = getAllSelectedItems();
        if (selectedItems.length > 0) {
            deleteButton.style.display = 'block';
            deleteButton.disabled = false;
        } else {
            deleteButton.style.display = 'none';
            deleteButton.disabled = true;
        }

        const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
        if (showSelectedButton) {  // ここで要素が存在するか確認
            showSelectedButton.style.display = anyChecked ? 'block' : 'none';
            showSelectedButton.disabled = !anyChecked;
        }
    }

    // 状態の復元
    function restoreState() {
        const isSelectAllChecked = getSelectAllState(currentPage);
        if (selectAllCheckbox) {  // ここで要素が存在するか確認
            selectAllCheckbox.checked = isSelectAllChecked;
        }

        const selectedItems = getSelectedItemsForPage(currentPage);
        const uncheckedItems = getUncheckedItemsForPage(currentPage);

        checkboxes.forEach(checkbox => {
            if (uncheckedItems.includes(checkbox.value)) {
                checkbox.checked = false;
            } else if (selectedItems.includes(checkbox.value)) {
                checkbox.checked = true;
            } else {
                checkbox.checked = isSelectAllChecked;
            }
        });

        updateButtonStates();
    }

    // ページが変わるたびに状態を復元
    $(document).on('pjax:complete', function () {
        restoreState();
        updateButtonStates();
    });

    // 削除ボタンのイベントハンドラ
    if (deleteButton) {  // ここで要素が存在するか確認
    deleteButton.addEventListener('click', function (event) {
        if (!confirm('本当に削除しますか？ この操作は元に戻せません。')) {
            event.preventDefault();
            return;
        }

        // ボタンの2重クリック防止のため無効化
        deleteButton.disabled = true;

        const selectedItems = getAllSelectedItems();

        fetch("{{ route('items.deleteSelected') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ selectedItems })
        })
    //     // .then(response => response.json())
    .then(response => {
        // ここでエラーレスポンスをチェック
        if (!response.ok) {
            throw new Error('削除リクエストが失敗しました');
        }
        return response.json();
    })
        .then(data => {
            alert(data.message);

            // 削除後、ローカルストレージから全選択状態を削除し、全ページの選択解除を実行
            for (let i = 1; i <= localStorage.length; i++) {
                localStorage.removeItem(`selected_items_page_${i}`);
                localStorage.removeItem(`unchecked_items_page_${i}`);
                localStorage.removeItem(`select_all_checked_page_${i}`);
            }

        // ボタンを非表示にする
        const showSelectedButton = document.getElementById('showSelectedButton');
        console.log(showSelectedButton);  // これで null ではないか確認

        if (showSelectedButton) {
            showSelectedButton.style.display = 'none';
        } else {
          console.error('Error: showSelectedButton not found');
        }

        localStorage.clear();


        // 削除が成功したらリストページにリダイレクト
        window.location.href = '{{ route('items.reset') }}';  // リスト画面のURLにリダイレクト
        })
        // .catch(error => console.error('Error:', error));
        .catch(error => {
        console.error('Error:', error);
        alert('削除に失敗しました: ' + error.message);
        deleteButton.disabled = false; // エラー時にボタンを再度有効化
    });



//     .then(response => response.text())  // JSONではなくtextとして一旦取得
// .then(data => {
//     console.log(data);  // ここでサーバーからのレスポンスを確認
//     try {
//         const jsonData = JSON.parse(data);
//         alert(jsonData.message);
//     } catch (error) {
//         console.error('JSON parse error:', error);
//         alert('削除に失敗しました: レスポンスが正しくありません。');
//     }
//      // 削除が成功したら一覧ページにリダイレクト
//      window.location.href = '{{ route('items.index') }}'; // 一覧ページのURLにリダイレクト
// })
// .catch(error => {
//     console.error('Error:', error);
//     alert('削除に失敗しました: ' + error.message);
// });
    });
}

    // 「選択している項目を表示」ボタンのイベントハンドラ
    showSelectedButton.addEventListener('click', function (event) {
        event.preventDefault();
        const isSelectAllChecked = getSelectAllState(currentPage);
        let selectedItems = [];

        if (isSelectAllChecked) {
            selectedItems = getAllSelectedItems();
        } else {
            selectedItems = getSelectedItemsForPage(currentPage);
        }

        const form = document.getElementById('showSelectedForm');
        const existingInput = document.querySelector('input[name="selected_items"]');
        if (existingInput) {
            existingInput.remove();
        }

        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'selected_items';
        hiddenInput.value = JSON.stringify(selectedItems);
        form.appendChild(hiddenInput);

        form.submit();
    });

    // 初期化
    restoreState();
});





// 選択されている項目を表示のボタン操作
document.addEventListener('DOMContentLoaded', function () {
    const showSelectedButton = document.getElementById('showSelectedButton');
    const form = document.getElementById('showSelectedForm');
    const selectAllCheckbox = document.getElementById('selectAll'); // 全選択のチェックボックス
    const checkboxes = document.querySelectorAll('.item-checkbox'); // 各アイテムのチェックボックス

    // 現在のURLを確認し、`/items/show-selected`にいる場合はボタンを非表示にする
    if (window.location.pathname.includes('/items/show-selected')) {
        if (showSelectedButton) {
            showSelectedButton.style.display = 'none';
        }
    }

    // ローカルストレージに選択された項目を保存・更新する関数
    function saveSelectedItems(checkbox) {
        const itemId = checkbox.value;
        let selectedItems = JSON.parse(localStorage.getItem('selected_items')) || [];

        if (checkbox.checked) {
            // チェックされた場合、アイテムを追加
            if (!selectedItems.includes(itemId)) {
                selectedItems.push(itemId);
            }
        } else {
            // チェックが外れた場合、アイテムを削除
            selectedItems = selectedItems.filter(item => item !== itemId);
        }

        localStorage.setItem('selected_items', JSON.stringify(selectedItems));

        // 選択されたアイテムをセッションに送信
        sendSelectedItemsToSession(selectedItems);
    }

    // 全選択状態をローカルストレージに保存
    function saveAllSelectedItems(isChecked) {
        let selectedItems = JSON.parse(localStorage.getItem('selected_items')) || [];

        if (isChecked) {
        // 全選択された場合、すべてのチェックボックスの値を取得して保存
        const allItems = Array.from(checkboxes).map(checkbox => checkbox.value);
        allItems.forEach(item => {
            if (!selectedItems.includes(item)) {
                selectedItems.push(item); // 新しいアイテムを選択に追加
            }
        });
        } else {
            // 全選択解除された場合、現在のページのアイテムをすべて除去
            const currentItems = Array.from(checkboxes).map(checkbox => checkbox.value);
            selectedItems = selectedItems.filter(item => !currentItems.includes(item));
        }

        // ローカルストレージに更新したリストを保存
        localStorage.setItem('selected_items', JSON.stringify(selectedItems));

        // サーバーに選択状態を保存
        sendSelectedItemsToSession(selectedItems);
        }


        // ローカルストレージの選択状態をセッションに送信
        function sendSelectedItemsToSession(selectedItems) {
        fetch("{{ route('items.saveSelectedItems') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ selectedItems })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok.');
            }
            return response.json();
        })
        .then(data => {
            console.log('Success:', data);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }

    // チェックボックスの選択状態に応じてボタンの表示/非表示と有効/無効を切り替える関数
    function updateShowSelectedButtonState() {
        // 現在のURLが`/items/show-selected`の場合はボタンを常に非表示にする
        if (window.location.pathname.includes('/items/show-selected')) {
            showSelectedButton.style.display = 'none';   // ボタンを非表示
            showSelectedButton.disabled = true;          // ボタンを無効化
            return; // 以降の処理をスキップ
        }
    
    const storedItems = JSON.parse(localStorage.getItem('selected_items')) || []; // ローカルストレージの状態を確認
        if (storedItems.length > 0) {
            showSelectedButton.style.display = 'block';  // ボタンを表示
            showSelectedButton.disabled = false;         // ボタンを有効化
        } else {
            showSelectedButton.style.display = 'none';   // ボタンを非表示
            showSelectedButton.disabled = true;          // ボタンを無効化
        }
    }

// 選択状態をフォームに追加してサーバーに送信する関数
function prepareFormSubmission() {
        // localStorage から選択されたアイテムを取得
        const selectedItems = JSON.parse(localStorage.getItem('selected_items')) || [];

        // 既存の hidden input があれば削除
        const existingInput = document.querySelector('input[name="selected_items"]');
        if (existingInput) {
            existingInput.remove();
        }

        // hidden input に選択アイテムを追加
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'selected_items';
        hiddenInput.value = JSON.stringify(selectedItems); // 選択アイテムを JSON として保存
        form.appendChild(hiddenInput);
    }


    // 現在のページ番号を取得
    function getCurrentPageNumber() {
      // ボタンをクリックしたときは常に1ページ目にリセット
      return '1';
}

    // ボタンがクリックされたときにフォーム送信を準備
showSelectedButton.addEventListener('click', function (event) {
    event.preventDefault(); // デフォルトの送信を防ぐ

    // ローカルストレージから選択されたアイテムを取得
    const selectedItems = JSON.parse(localStorage.getItem('selected_items')) || [];
    // コンソールに選択アイテムを表示（デバッグ用）
    console.log('Selected Items:', selectedItems);

    prepareFormSubmission();  // 選択された項目をフォームに追加

    //新しいフォームを作成
    const form = document.createElement('form');
    form.method = 'POST'; // POST メソッドで送信
    form.action = '/items/show-selected'; // サーバー側の POST エンドポイントを指定

    // CSRF トークンを追加
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';  // サーバー側で生成されたトークンを使用
    form.appendChild(csrfInput);

    // 選択されたアイテムをフォームに追加
    const hiddenItems = JSON.parse(localStorage.getItem('selected_items')) || [];
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'selected_items';
    hiddenInput.value = JSON.stringify(selectedItems);
    form.appendChild(hiddenInput);

    // ページ番号を追加
    const pageInput = document.createElement('input');
    pageInput.type = 'hidden';
    pageInput.name = 'page';
    pageInput.value = getCurrentPageNumber(); // ページ番号の取得関数
    form.appendChild(pageInput);

    // フォームを DOM に追加して送信
    document.body.appendChild(form);
    form.submit();
    });

    // 全選択チェックボックスのイベントリスナー
    selectAllCheckbox.addEventListener('change', function () {
        const isChecked = this.checked;

        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            saveSelectedItems(checkbox);
        });
        saveAllSelectedItems(isChecked);
        updateShowSelectedButtonState();
    });

    // チェックボックスの状態を復元し、イベントリスナーを追加する関数
    function restoreCheckboxStateAndBindEvents() {
        const checkboxes = document.querySelectorAll('.item-checkbox'); // 最新のチェックボックスを取得
        const storedItems = JSON.parse(localStorage.getItem('selected_items')) || [];

        checkboxes.forEach(checkbox => {
            // ローカルストレージのデータに基づいてチェックボックスの状態を復元
            if (storedItems.includes(checkbox.value)) {
                checkbox.checked = true;
            }

            // チェックボックスの状態が変更されたら、ローカルストレージとボタンの状態を更新
            checkbox.addEventListener('change', function () {
                saveSelectedItems(checkbox);
                updateShowSelectedButtonState(); // ボタンの表示と有効/無効状態を更新
            });
        });

        // ボタンの表示と有効/無効状態を初期化
        updateShowSelectedButtonState(); // ローカルストレージに基づいてボタンの状態を更新
    }


    // ページネーションが完了したら、チェックボックスの選択状態を復元し、イベントリスナーを再設定
    $(document).on('pjax:complete', function () {
        requestAnimationFrame(function () {
            restoreCheckboxStateAndBindEvents(); // ページネーション後に再初期化
        }); // 非同期処理のタイミング調整
    });

    // 初期化
    restoreCheckboxStateAndBindEvents();
});






</script>
@stop