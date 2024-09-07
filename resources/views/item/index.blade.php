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

        @if(request('sort') || request('order'))
    <div>
        <a href="{{ url('/items') }}" class="btn btn-primary mb-3">アプリ情報一覧に戻る</a>
    </div>
    @endif

        <!-- 並び替えフォーム -->
    <form action="{{ url('items') }}" method="GET" id="sortForm" class="form-inline">
        <div class="form-group">
            <!-- 並び替え基準の選択 -->
            <select name="sort" id="sort" class="form-control mr-1" style="padding: 2px;">
                <option value="" disabled selected>並び替え選択</option>
                <option value="stock" {{ request('sort') == 'stock' ? 'selected' : '' }}>ダウンロード数</option>
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
    </form>

        <div class="card mt-5">
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
</div>
<div class="d-flex justify-content-center">
{{ $items->appends(request()->query())->links('pagination::bootstrap-4') }}
</div>
</div>
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
            searchType.value = ''; // "選択してください" に戻す
            keywordSearch.querySelector('input[name="keyword"]').value = ''; // キーワード検索フィールドをリセット
            priceRangeSearch.querySelector('input[name="upper"]').value = ''; // 上限値フィールドをリセット
            priceRangeSearch.querySelector('input[name="lower"]').value = ''; // 下限値フィールドをリセット
            downloadRangeSearch.querySelector('input[name="download_upper"]').value = ''; // 上限ダウンロード数/万フィールドをリセット
            downloadRangeSearch.querySelector('input[name="download_lower"]').value = ''; // 下限ダウンロード数/万フィールドをリセット
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
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

// 並び替え基準が選択された時、順序をリセットしユーザーに選ばせる
    document.getElementById('sort').addEventListener('change', function() {
        const orderSelect = document.getElementById('order');
        orderSelect.value = ''; // 順序選択をリセット
        orderSelect.disabled = false; // 選択可能にする
    });

    // 並び替え順序の選択時に自動的にフォームを送信
    document.getElementById('order').addEventListener('change', function() {
        if (this.value && document.getElementById('sort').value) {
            document.getElementById('sortForm').submit();
        }
    });

    // ページ読み込み時に既に選択されている場合の処理
    document.addEventListener('DOMContentLoaded', function() {
        const sortValue = document.getElementById('sort').value;
        const orderSelect = document.getElementById('order');
        if (sortValue) {
            orderSelect.disabled = false; // 選択可能にする
        } else {
            orderSelect.disabled = true; // 選択不可にする
        }
    });
</script>
@stop