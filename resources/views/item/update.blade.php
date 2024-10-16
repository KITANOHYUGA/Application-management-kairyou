@extends('adminlte::page')

@section('title', 'アプリ登録')

@section('content_header')
    <h1>編集画面</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-10">
            <!-- @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                       @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                       @endforeach
                    </ul>
                </div>
            @endif -->

            <a href="{{ url('/items') }}" class="btn btn-primary mb-3">アプリ情報一覧に戻る</a>
            <div class="card card-primary">
                <form method="POST" action="{{ url('items/updateItem/'.$item->id) }}" onsubmit="return confirmSave()" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">アプリ名:</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $item->name) }}" placeholder="アプリ名">
                        </div>

                        <div class="form-group">
                            <label for="type">カテゴリー選択:</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">カテゴリーを選択</option>
                                <option value="1" {{ old('type', $item->type) == 1 ? 'selected' : '' }}>1.ゲーム</option>
                                <option value="2" {{ old('type', $item->type) == 2 ? 'selected' : '' }}>2.教育</option>
                                <option value="3" {{ old('type', $item->type) == 3 ? 'selected' : '' }}>3.ユーティリティー</option>
                                <option value="4" {{ old('type', $item->type) == 4 ? 'selected' : '' }}>4.スポーツ</option>
                                <option value="5" {{ old('type', $item->type) == 5 ? 'selected' : '' }}>5.ロールプレイング</option>
                                <option value="6" {{ old('type', $item->type) == 6 ? 'selected' : '' }}>6.その他</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="icon">アイコン画像:</label>               
                             @if ($item->icon)
                            <div class="mb-3"> 
                                <!-- アイコンが既にある場合は画像を表示 -->
                                    <img src="{{ asset('storage/' . $item->icon) }}" class="img-thumbnail rounded-circle" style="width: 60px; height: 60px; object-fit: cover; ">
                            </div>
                            @endif
                                <!-- ファイル選択は常に表示 -->
                                <div class="custom-file" style="margin-bottom: 8px;">
                                    <input type="file" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" style="padding-top: 4px; padding-bottom: 4px;">
                                </div>
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>

                        <div class="form-group">
                            <label for="company_id">会社名:</label>
                            <select class="form-control @error('company_id') is-invalid @enderror" id="company_id" name="company_id">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', $item->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->company_name }}
                                    </option>
                                @endforeach
                                <option value="change" {{ old('company_id', $item->company_id) == 'change' ? 'selected' : '' }}>会社名を変更</option>
                            </select>
                            @error('company_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name') }}" placeholder="会社名を入力してください">
                        </div>

                        <div class="form-group">
                                <label for="price">価格/円:</label>
                                    <div>
                                        <label><input type="radio" name="price_option" value="free" {{ old('price_option', $item->price == 0 ? 'free' : 'custom') == 'free' ? 'checked' : '' }}> 無料</label>
                                        <label><input type="radio" name="price_option" value="custom" {{ old('price_option', $item->price == 0 ? 'free' : 'custom') == 'custom' ? 'checked' : '' }}> 価格を変更</label>
                                    </div>
                                <input type="number" name="price" id="price" class="form-control  @error('price') is-invalid @enderror" style="display: {{ old('price_option', $item->price == 0 ? 'free' : 'custom') == 'custom' ? 'block' : 'none' }};" value="{{ old('price', $item->price > 0 ? $item->price : '') }}">
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>

                        <div class="form-group">
                            <label for="dawnload">ダウンロード数/万:</label>
                            <input type="text" class="form-control @error('dawnload') is-invalid @enderror" id="dawnload" name="dawnload" value="{{ old('dawnload', $item->dawnload) }}" placeholder="ダウンロード数/万">
                            @error('dawnload')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="comment">コメント:</label>
                            <input type="text" class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" value="{{ old('comment', $item->comment) }}" placeholder="コメント">
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer d-flex">
                            <button type="submit" class="btn btn-primary">保存</button>
                        </form>

                        <form action="{{ url('items/deleteItem/' . $item->id) }}" method="POST" onsubmit="return confirmDelete();" class="ml-auto">
                            @csrf
                            @method('DELETE') <!-- DELETEメソッドを指定 -->
                            <button type="submit" class="btn btn-danger">削除</button>
                        </form>
                    </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var companySelect = document.getElementById('company_id');
        var companyNameField = document.getElementById('company_name');
        var priceOptionRadios = document.getElementsByName('price_option');
        var priceInput = document.getElementById('price');

        function toggleCompanyNameField() {
            if (companySelect.value === 'change') {
                companyNameField.style.display = 'block';
            } else {
                companyNameField.style.display = 'none';
            }
        }

        function togglePriceInput() {
            const selectedOption = document.querySelector('input[name="price_option"]:checked');
            if (selectedOption && selectedOption.value === 'custom') {
                priceInput.style.display = 'block';
            } else {
                priceInput.style.display = 'none';
            }
        }

        companySelect.addEventListener('change', toggleCompanyNameField);

        for (const radio of priceOptionRadios) {
            radio.addEventListener('change', togglePriceInput);
        }

        // 初期状態で表示/非表示を設定
        toggleCompanyNameField();
        togglePriceInput();
    });

        function confirmDelete() {
        return confirm('本当に削除しますか？');
    }

    // 保存ボタンが押されたときに確認ダイアログを表示
    function confirmSave() {
    return confirm("編集を完了しますか？");
    }

    </script>
@stop