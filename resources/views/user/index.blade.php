@extends('adminlte::page')

@section('title', 'ユーザー管理システム')

@section('content')
    <div class="container">
        <div class="p-3">
            <h1>ユーザー一覧</h1>
        </div>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- 検索フォーム -->

        <form action="{{ url('users/search') }}" method="GET" id="searchForm">
        @csrf
        <div class="d-flex mb-3">
            <div id="userSearch" style="width: 300px;">
                <input placeholder="ユーザー名で検索" type="text" name="userName" class="form-control" id="userNameInput" value="{{ request('userName') }}">
            </div>
            <div class="col-auto" style="">
                <input type="submit" value="検索" class="btn btn-primary">
            </div>

            <div class="col-auto ml-auto">
                <button type="button" id="clearSearchButton" class="btn btn-outline-secondary" style="display: none;">クリア</button>
            </div>
        </div>
        </form>

        <!-- ユーザー一覧テーブル -->
        <form id="bulk-delete-form" action="{{ route('users.bulkDelete') }}" method="POST" onsubmit="return confirmDeletion()">
            @csrf
            <div class="table-responsive">
                <table class="table table-hover table-striped table-bordered align-middle text-center">
                    <thead class="table-dark">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th scope="col">ID</th>
                            <th scope="col">名前</th>
                            <th scope="col">メールアドレス</th>
                            <th scope="col">権限</th>
                            <th scope="col">登録日</th>
                            <th scope="col">更新日</th>
                            <th scope="col">操作</th>
                        </tr>
                    </thead>
                    <tbody id="user-table-body">
                        @foreach($users as $user)
                            <tr>
                                <td><input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox"></td>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->auth == 1 ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $user->auth == 1 ? '管理者' : '一般ユーザー' }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                <td>{{ $user->updated_at->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">編集</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- 選択したユーザーを削除ボタン -->
            <button type="submit" id="deleteButton" class="btn btn-danger mt-3" style="display: none;">選択したユーザーを削除</button>
        </form>

        <!-- ページネーション -->
        <div class="d-flex justify-content-center mt-4">
            {{ $users->links('pagination::bootstrap-4') }}  <!-- Bootstrap 4スタイルでページネーションをレンダリング -->
        </div>
    </div>
    @stop

    <!-- 検索機能スクリプト -->
    @section('js')
    <script>
    document.getElementById('select-all').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('input[name="user_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    });

    // 確認ダイアログを表示するスクリプト
    function confirmDeletion() {
        return confirm('選択したユーザーを本当に削除してもよろしいですか？');
    }

    document.addEventListener("DOMContentLoaded", function() {
        const pageId = '{{ request()->get("page", 1) }}'; // 現在のページ番号を取得
        const storageKey = `selectedUserIds_${pageId}`;
        const selectAllKey = `selectAllChecked_${pageId}`;
        const userNameInput = document.getElementById('userNameInput');
        const clearSearchButton = document.getElementById('clearSearchButton');
        const deleteButton = document.getElementById('deleteButton');
        const selectAllCheckbox = document.getElementById('select-all');
        const menuLinks = document.querySelectorAll('.nav-link'); // AdminLTEで生成されるメニューリンクを取得

        // 各メニューリンクにクリックイベントを追加
        menuLinks.forEach(link => {
        link.addEventListener('click', function (event) {
            const href = link.getAttribute('href');
            
            // サブメニューがあるメインメニュー項目の href は '#'
            if (href === '#') {
                event.preventDefault(); // リダイレクトを防止
                
                // サブメニューの要素を取得
                const submenu = link.nextElementSibling; 
                if (submenu && submenu.classList.contains('submenu')) {
                    submenu.classList.toggle('open'); // サブメニューの開閉を切り替え
                }
            }

            
            // クリックされたリンクをデバッグログに出力
            console.log('クリックされたリンク:', link);

            // 選択状態をクリアする関数を呼び出し
            clearAllPageSelections();  // ページ遷移前に選択状態をクリア
            sessionStorage.removeItem('disableRestore'); // 状態復元を無効にするためのフラグをクリア

        });
    });

    function clearAllPageSelections() {
        // ローカルストレージとセッションストレージのチェック状態を削除
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith('selectedUserIds_') || key.startsWith('selectAllChecked_')) {
                localStorage.removeItem(key);
            }
        }
        
        for (let i = 0; i < sessionStorage.length; i++) {
            const key = sessionStorage.key(i);
            if (key.startsWith('selectedUserIds_') || key.startsWith('selectAllChecked_')) {
                sessionStorage.removeItem(key);
            }
        }
    }

        // ページロード時に「全選択」チェックボックスの状態を復元
        if (localStorage.getItem(selectAllKey) === 'true') {
            selectAllCheckbox.checked = true;
        }

        // チェックボックス全選択・解除
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                handleCheckboxChange(checkbox);
            });

            // 現在のページの「全選択」チェックボックスの状態を保存
            localStorage.setItem(selectAllKey, this.checked);
            toggleButtons();
        });

        // 各ユーザーチェックボックスのイベント設定
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                handleCheckboxChange(this);
                toggleButtons();

                // 個別のチェックが外れた場合、「全選択」チェックボックスも外す
                if (!this.checked) {
                    selectAllCheckbox.checked = false;
                    localStorage.setItem(selectAllKey, false);
                }
            });
        });

        clearSearchButton.addEventListener('click', function(event) {
            event.preventDefault();
            clearSelection();
            resetToInitialState();
        });

        deleteButton.addEventListener('click', function(event) {
            event.preventDefault();
            
            // 削除確認のポップアップ表示
            const userConfirmed = confirm("選択した項目を本当に削除しますか？");
            if (!userConfirmed) {
                // ユーザーがキャンセルを選択した場合、処理を中断
                return;
            }
    
            // 各ページの選択されたIDを収集
            let allSelectedIds = [];
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
            if (key.startsWith('selectedUserIds_')) {
                const ids = JSON.parse(localStorage.getItem(key));
                allSelectedIds.push(...ids);
                }
            }

            if (allSelectedIds.length > 0) {
                console.log("削除対象のユーザーID:", allSelectedIds);  // デバッグ用

                fetch('/users/bulk-delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ user_ids: allSelectedIds })
                }).then(response => {
                    if (response.ok) {
                        alert("選択したユーザーが削除されました。");
                        clearAllSelections();  // 削除完了後に選択状態をクリア
                        window.location.href = '/users/list?page=1'; // 1ページ目にリダイレクト
                    } else {
                        console.error("削除リクエストが失敗しました。");
                    }
                }).catch(error => {
                    console.error("リクエスト中にエラーが発生しました:", error);
                });
            } else {
                alert("削除するユーザーが選択されていません。");
            }
        });

        // 削除完了後にローカルストレージの選択状態をクリア
        function clearAllSelections() {
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key.startsWith('selectedUserIds_') || key.startsWith('selectAllChecked_')) {
                    localStorage.removeItem(key);
                }
            }
        }

        // 検索フォームの送信時にチェックボックスをリセット
        document.getElementById('searchForm').addEventListener('submit', function() {
            clearSelection();
        });

        // チェックボックスの状態をlocalStorageに保存
        function handleCheckboxChange(checkbox) {
            const selectedIds = getSelectedUserIds();
            const userId = checkbox.value;

            if (checkbox.checked) {
                if (!selectedIds.includes(userId)) {
                    selectedIds.push(userId);
                }
            } else {
                const index = selectedIds.indexOf(userId);
                if (index > -1) {
                    selectedIds.splice(index, 1);
                }
            }
            localStorage.setItem(storageKey, JSON.stringify(selectedIds));
        }

        // 選択されたユーザーIDリストを取得
        function getSelectedUserIds() {
            return JSON.parse(localStorage.getItem(storageKey) || '[]');
        }

        // 削除ボタン・クリアボタンの表示を切り替え
        function toggleButtons() {
            const anyChecked = document.querySelectorAll('.user-checkbox:checked').length > 0;
            const hasSearchInput = userNameInput && userNameInput.value.trim() !== '';

            clearSearchButton.style.display = (anyChecked || hasSearchInput) ? 'inline-block' : 'none';
            deleteButton.style.display = anyChecked ? 'inline-block' : 'none';
        }

        // 全てのチェックボックスをオフにしてローカルストレージをクリア
        function clearSelection() {
        fetch('/users/clear-selection', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ clearSelection: true })
    }).then(response => {
        if (response.ok) {
            // フロントエンドの選択状態もリセット
            document.querySelectorAll('.user-checkbox').forEach(checkbox => checkbox.checked = false);
            document.getElementById('select-all').checked = false;

           // 全ページのローカルストレージ/セッションストレージのデータを削除
           for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                if (key.startsWith('selectedUserIds_') || key.startsWith('selectAllChecked_')) {
                    localStorage.removeItem(key);
                }
            }
            
            for (let i = 0; i < sessionStorage.length; i++) {
                const key = sessionStorage.key(i);
                if (key.startsWith('selectedUserIds_') || key.startsWith('selectAllChecked_')) {
                    sessionStorage.removeItem(key);
                }
            }

            // ボタンの表示も初期化
            toggleButtons();
        } else {
            console.error("サーバー側で選択状態のリセットに失敗しました。");
        }
    }).catch(error => console.error("リクエスト中にエラーが発生しました:", error));
}

        // 初期状態に戻す
        function resetToInitialState() {
            if (userNameInput) userNameInput.value = '';
            document.getElementById('searchForm').submit();
        }

        // ページロード時にチェックボックスの状態を復元
        restoreCheckboxStates();

        function restoreCheckboxStates() {
            const selectedIds = getSelectedUserIds();
            const selectAllChecked = localStorage.getItem(selectAllKey) === 'true';
            selectAllCheckbox.checked = selectAllChecked;

            document.querySelectorAll('.user-checkbox').forEach(checkbox => {
                checkbox.checked = selectAllChecked || selectedIds.includes(checkbox.value);
            });

            toggleButtons();
        }

    });

    

    </script>
@stop
