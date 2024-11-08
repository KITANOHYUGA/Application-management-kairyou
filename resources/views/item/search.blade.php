@extends('adminlte::page')

@section('title', 'アプリ情報検索')

@section('content_header')
    <h1>アプリ情報検索</h1>
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
         <!-- 検索後に「アプリ情報一覧に戻る」リンクを表示 -->
        @if(request('keyword') || request('upper') || request('lower') || request('dawnload_upper') || request('dawnload_lower'))
                <div>
                    <a href="{{ url('/items/reset') }}"  class="btn btn-primary mb-3">アプリ情報一覧に戻る</a>
                </div>
        @endif

            <form action="{{ url('items/search') }}" method="GET">
                @csrf
                <div class="form-group">
                    <div class="form-row align-items-center d-flex">
                        <div class="col-auto">
                            <label for="search_type">検索方法を選択:</label>
                            <select id="search_type" name="search_type" class="form-control mr-2" style="padding: 2px; width: 300px">
                                <option value="" selected>選択してください</option>
                                <option value="keyword" {{ request('search_type') == 'keyword' ? 'selected' : '' }}>キーワード検索</option>
                                <option value="price_range" {{ request('search_type') == 'price_range' ? 'selected' : '' }} style="width: 250px">価格範囲で検索</option>
                                <option value="dawnload_range" {{ request('search_type') == 'dawnload_range' ? 'selected' : '' }} style="width: 250px">ダウンロード数/万範囲で検索</option>
                            </select>
                        </div>

                        <div class="col-auto" style="padding-top: 32px;">
                            <input type="submit" value="検索" class="btn btn-primary">
                        </div>

                        <div class="col-auto ml-auto" style="padding-top: 32px;">
                        <button type="button" id="clearSearchButton" class="btn btn-outline-secondary ml-1" style="display: none;">クリア</button>
                        </div>
                    </div>

                    <!-- キーワード検索フィールド -->
                    <div id="keyword_search" style="display: none; margin-top: 5px; width: 300px;">
                        <input placeholder="キーワードを入力" type="text" name="keyword" class="form-control mb-2" value="{{ request('keyword') }}">
                    </div>

                    <!-- 価格範囲検索フィールド -->
                    <div id="price_range_search" style="display: none; margin-top: 5px; width: 300px;">
                        <input placeholder="上限値を入力"  type="text" name="upper" class="form-control mb-2" value="{{ request('upper') }}">
                        <input placeholder="下限値を入力"  type="text" name="lower" class="form-control mb-2" value="{{ request('lower') }}">
                    </div>

                    <!-- ダウンロード数/万範囲検索フィールド -->
                    <div id="dawnload_range_search" style="display: none; margin-top: 5px; width: 300px;">
                        <input placeholder="上限ダウンロード数/万を入力" type="text" name="dawnload_upper" class="form-control mb-2" value="{{ request('dawnload_upper') }}">
                        <input placeholder="下限ダウンロード数/万を入力" type="text" name="dawnload_lower" class="form-control mb-2" value="{{ request('dawnload_lower') }}">
                    </div>
                </div>
            </form>
        


            <!-- アプリ情報一覧の表示 -->
            @if (!$errors->any())
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th style="visibility: hidden;">アプリ画像</th>
                                <th>アプリ名</th>
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
                                    <td class="align-middle">
                                    @if ($item->icon)
                                        <img src="{{ asset('storage/' . $item->icon) }}" class="img-thumbnail rounded-circle" style="width: 60px; height: 60px; object-fit: cover; ">
                                        @else
                                        <img src="{{ asset('storage/icons/default.png') }}" class="img-thumbnail rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                        @endif
                                    </td>
                                    <td class="align-middle">{{ $item->name }}</td>
                                    <td class="align-middle">{{ $item->company->company_name }}</td>
                                    <td class="align-middle">{{ $item->price == 0 ? '無料' : $item->price.'円' }}</td>
                                    <td class="align-middle">{{ $item->dawnload }}万</td>
                                    <td class="align-middle">{{ $item->comment }}</td>
                                    <td class="align-middle">
                                    @if(auth()->user()->auth == 1)
                                            <form action="{{ url('items/update/'.$item->id) }}" method="get">
                                                @csrf
                                                 <button type="submit" class="btn btn-outline-primary btn-sm">編集</button>
                                             </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6">該当するアプリがありません</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<!-- アイテムのリスト表示 -->
<!-- ページネーションリンク -->
<div class="d-flex justify-content-center mt-4">
{{ $items->appends(request()->query())->links('pagination::bootstrap-4') }}
</div>
    @endif
@stop

@section('css')
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // デフォルトの状態に戻す
    let searchType = document.getElementById('search_type');
    let keywordSearch = document.getElementById('keyword_search');
    let priceRangeSearch = document.getElementById('price_range_search');
    let dawnloadRangeSearch = document.getElementById('dawnload_range_search');
    let searchForm = document.getElementById('search_form');
    let priceInput = document.getElementById('price');
    let priceOptionRadios = document.getElementsByName('price_option');

    if (searchType) {
        // 検索タイプが選択された時に表示されるフィールドを切り替える
        searchType.addEventListener('change', function() {
            // 検索タイプが変更されたときにすべてのフィールドをリセット
            resetFields();
            if (this.value === 'keyword') {
                keywordSearch.style.display = 'block';
                priceRangeSearch.style.display = 'none';
                dawnloadRangeSearch.style.display = 'none';
            } else if (this.value === 'price_range') {
                keywordSearch.style.display = 'none';
                priceRangeSearch.style.display = 'block';
                dawnloadRangeSearch.style.display = 'none';
            } else if (this.value === 'dawnload_range') {
                keywordSearch.style.display = 'none';
                priceRangeSearch.style.display = 'none';
                dawnloadRangeSearch.style.display = 'block';
            } else {
                keywordSearch.style.display = 'none';
                priceRangeSearch.style.display = 'none';
                dawnloadRangeSearch.style.display = 'none';
            }
        });

        // 検索タイプがすでに選択されている場合は表示状態を初期化
        const currentSearchType = searchType.value;
        if (currentSearchType === 'keyword') {
            keywordSearch.style.display = 'block';
        } else if (currentSearchType === 'price_range') {
            priceRangeSearch.style.display = 'block';
        } else if (currentSearchType === 'dawnload_range') {
            dawnloadRangeSearch.style.display = 'block';
        }

        // フィールドをリセットする関数
        function resetFields() {
        // 各検索フィールドの値をクリア
        if (keywordSearch) keywordSearch.querySelector('input').value = '';
        if (priceRangeSearch) priceRangeSearch.querySelector('input').value = '';
        if (dawnloadRangeSearch) dawnloadRangeSearch.querySelector('input').value = '';
        }
    }

    // price_option ラジオボタンの処理
    if (priceInput && priceOptionRadios.length > 0) {
        function togglePriceInput() {
            const selectedOption = document.querySelector('input[name="price_option"]:checked');
            if (selectedOption && selectedOption.value === 'custom') {
                priceInput.style.display = 'block';
            } else {
                priceInput.style.display = 'none';
                priceInput.value = ''; // 無料が選択された場合、価格フィールドをリセット
            }
        }

        for (const radio of priceOptionRadios) {
            radio.addEventListener('change', togglePriceInput);
        }

        // 初期状態で表示/非表示を設定
        togglePriceInput();
    }
});
    // ページが完全に読み込まれた後に実行
    document.addEventListener('DOMContentLoaded', function() {
        // 検索タイプのセレクトボックス
        const searchTypeSelect = document.getElementById('search_type');
        // クリアボタン
        const clearButton = document.getElementById('clearSearchButton');

    // searchTypeSelectやclearButtonが存在するかを確認
    if (searchTypeSelect && clearButton) {
        // ページ読み込み時に検索タイプが選択されていればクリアボタンを表示
        if (searchTypeSelect.value !== '') {
            clearButton.style.display = 'inline-block';
        }

            // クリアボタンが押されたときの動作
            clearButton.addEventListener('click', function() {
                // フォームのリセット
                document.querySelector('form').reset();

                // 初期一覧ページにリダイレクト
                window.location.href = "{{ url('/items/searchReset') }}";
            });
        } else {
            console.error('search_type または clearButton が見つかりません');
        }
    });

    </script>
@stop
