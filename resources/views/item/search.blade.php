@extends('adminlte::page')

@section('title', 'アプリ情報検索')

@section('content_header')
    <h1>アプリ情報一覧検索</h1>
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
        @if(request('keyword') || request('upper') || request('lower') || request('download_upper') || request('download_lower'))
                <div>
                    <a href="{{ url('/items') }}"  class="btn btn-primary mb-3">アプリ情報一覧に戻る</a>
                </div>
        @endif

            <form action="{{ url('items/search') }}" method="GET">
                @csrf
                <div class="form-group">
                    <div class="form-row align-items-center">
                        <div class="col-auto">
                            <label for="search_type">検索方法を選択:</label>
                            <select id="search_type" name="search_type" class="form-control" style="width: 300px;">
                                <option value="" selected>選択してください</option>
                                <option value="keyword" {{ request('search_type') == 'keyword' ? 'selected' : '' }}>キーワード検索</option>
                                <option value="price_range" {{ request('search_type') == 'price_range' ? 'selected' : '' }}>価格範囲で検索</option>
                                <option value="download_range" {{ request('search_type') == 'download_range' ? 'selected' : '' }}>ダウンロード数/万範囲で検索</option>
                            </select>
                        </div>

                        <div class="col-auto" style="padding-top: 32px;">
                            <input type="submit" value="検索" class="btn btn-primary">
                        </div>
                    </div>

                    <!-- キーワード検索フィールド -->
                    <div id="keyword_search" style="display: none; margin-top: 5px; width: 300px;">
                        <input placeholder="キーワードを入力" type="text" name="keyword" class="form-control mb-2">
                    </div>

                    <!-- 価格範囲検索フィールド -->
                    <div id="price_range_search" style="display: none; margin-top: 5px; width: 300px;">
                        <input placeholder="上限値を入力" type="text" name="upper" class="form-control mb-2">
                        <input placeholder="下限値を入力" type="text" name="lower" class="form-control mb-2">
                    </div>

                    <!-- ダウンロード数/万範囲検索フィールド -->
                    <div id="download_range_search" style="display: none; margin-top: 5px; width: 300px;">
                        <input placeholder="上限ダウンロード数/万を入力" type="text" name="download_upper" class="form-control mb-2">
                        <input placeholder="下限ダウンロード数/万を入力" type="text" name="download_lower" class="form-control mb-2">
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
                                <th>アプリ画像</th>
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
                                    <td class="align-middle">{{ $item->price == 0 ? '無料' : $item->price }}</td>
                                    <td class="align-middle">{{ $item->stock }}</td>
                                    <td class="align-middle">{{ $item->comment }}</td>
                                    <td class="align-middle">
                                        @if(\Illuminate\Support\Facades\Auth::id() === $item->user_id)
                                            <form action="{{ url('items/update/'.$item->id) }}" method="get">
                                                @csrf
                                                 <button type="submit" class="btn btn-outline-primary btn-sm">編集</button>
                                             </form>
                                        @else
                                            編集できません
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
    {{ $items->links('pagination::bootstrap-4') }}
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
            let downloadRangeSearch = document.getElementById('download_range_search');
            
            searchType.value = ''; // "選択してください" に戻す
            keywordSearch.style.display = 'none';
            priceRangeSearch.style.display = 'none';
            downloadRangeSearch.style.display = 'none';

            // 検索タイプが選択された時に表示されるフィールドを切り替える
            searchType.addEventListener('change', function() {
                if (this.value === 'keyword') {
                    keywordSearch.style.display = 'block';
                    priceRangeSearch.style.display = 'none';
                    downloadRangeSearch.style.display = 'none';
                } else if (this.value === 'price_range') {
                    keywordSearch.style.display = 'none';
                    priceRangeSearch.style.display = 'block';
                    downloadRangeSearch.style.display = 'none';
                } else if (this.value === 'download_range') {
                    keywordSearch.style.display = 'none';
                    priceRangeSearch.style.display = 'none';
                    downloadRangeSearch.style.display = 'block';
                } else {
                    keywordSearch.style.display = 'none';
                    priceRangeSearch.style.display = 'none';
                    downloadRangeSearch.style.display = 'none';
                }
            });

            // フォーム送信時に入力フィールドをリセット
            document.getElementById('search_form').addEventListener('submit', function() {
                searchType.value = '';  // "選択してください" に戻す
                keywordSearch.querySelector('input[name="keyword"]').value = ''; // キーワード検索フィールドをリセット
                priceRangeSearch.querySelector('input[name="upper"]').value = ''; // 上限値フィールドをリセット
                priceRangeSearch.querySelector('input[name="lower"]').value = ''; // 下限値フィールドをリセット
                downloadRangeSearch.querySelector('input[name="download_upper"]').value = ''; // 上限ダウンロード数/万フィールドをリセット
                downloadRangeSearch.querySelector('input[name="download_lower"]').value = ''; // 下限ダウンロード数/万フィールドをリセット
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            var priceOptionRadios = document.getElementsByName('price_option');
            var priceInput = document.getElementById('price');

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
});
    </script>
@stop
