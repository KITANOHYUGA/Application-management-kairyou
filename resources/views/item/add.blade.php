@extends('adminlte::page')

@section('title', 'アプリ登録')

@section('content_header')
    <h1>アプリ登録</h1>
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

            <a href="{{ route('items.reset') }}" class="btn btn-primary mb-3">アプリ情報一覧に戻る</a>

            <div class="card card-primary">
                <form method="POST" enctype="multipart/form-data" id="myForm">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">アプリ名:</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" placeholder="アプリ名" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>

                        <div class="form-group">
                            <label for="type">カテゴリー選択:</label>
                            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                <option value="">カテゴリーを選択</option>
                                <option value="1" {{ old('type') == '1' ? 'selected' : '' }}>1.ゲーム</option>
                                <option value="2" {{ old('type') == '2' ? 'selected' : '' }}>2.教育</option>
                                <option value="3" {{ old('type') == '3' ? 'selected' : '' }}>3.ユーティリティー</option>
                                <option value="4" {{ old('type') == '4' ? 'selected' : '' }}>4.スポーツ</option>
                                <option value="5" {{ old('type') == '5' ? 'selected' : '' }}>5.ロールプレイング</option>
                                <option value="6" {{ old('type') == '6' ? 'selected' : '' }}>6.その他</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        @if($companies->isEmpty())
                            <!-- 会社情報が空の場合、新しい会社名の入力フィールドのみ表示 -->
                            <div class="form-group" id="new_company">
                                <label for="company_name">会社名:</label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" placeholder="会社名" value="{{ old('company_name') }}" required>
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <!-- 会社情報が存在する場合、選択ボックスと新しい会社名入力フィールドの両方を表示 -->
                            <div class="form-group">
                                <label for="company_id">会社名:</label>
                                <select class="form-control @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                                    <option value="" selected>選択してください</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>{{ $company->company_name }}</option>
                                    @endforeach
                                    <option value="new" {{ old('company_id') == 'new' ? 'selected' : '' }}>会社名を追加</option>
                                </select>
                                @error('company_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group" id="new_company" style="display: old('company_id') == 'new' ? 'block' : 'none' }};">
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" placeholder="会社名" value="{{ old('company_name') }}">
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="icon">アイコン画像:</label>
                                <div class="custom-file" style="margin-bottom: 8px;">
                                    <input type="file" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" style="padding-top: 4px; padding-bottom: 4px;">
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                        </div>


                        <div class="form-group">
                            <label>価格/円:</label>
                                <div>
                                    <label><input type="radio" name="price_option" value="free" {{ old('price_option') == 'free' ? 'checked' : '' }}> 無料</label>
                                    <label><input type="radio" name="price_option" value="custom" {{ old('price_option') == 'custom' ? 'checked' : '' }}> 価格を入力</label>
                                </div>
                            <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" style="display: {{ old('price_option') == 'custom' ? 'block' : 'none' }}" value="{{ old('price') }}">
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="dawnload">ダウンロード数/万:</label>
                            <input type="text" class="form-control @error('dawnload') is-invalid @enderror" id="dawnload" name="dawnload" placeholder="ダウンロード数/万" value="{{ old('dawnload') }}" required>
                            @error('dawnload')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="comment">コメント</label>
                            <input type="text" class="form-control @error('comment') is-invalid @enderror" id="comment" name="comment" placeholder="コメント" value="{{ old('comment') }}">
                            @error('comment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary" id="submitButton">登録</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const companySelect = document.getElementById('company_id');
        const newCompanyField = document.getElementById('new_company');

        function updateNewCompanyFieldVisibility() {
            if (companySelect.value === 'new') {
                newCompanyField.style.display = 'block';
            } else {
                newCompanyField.style.display = 'none';
            }
        }

        if (companySelect) {
            companySelect.addEventListener('change', updateNewCompanyFieldVisibility);

            // 初期状態で表示/非表示を設定
            updateNewCompanyFieldVisibility();
        }

        // 価格オプションに応じて価格入力フィールドを表示
        const priceOptionRadios = document.getElementsByName('price_option');
        const priceInput = document.getElementById('price');

        for (const radio of priceOptionRadios) {
            radio.addEventListener('change', function() {
                if (this.value === 'custom') {
                    priceInput.style.display = 'block';
                } else {
                    priceInput.style.display = 'none';
                    priceInput.value = ''; // 無料が選択された場合、価格フィールドをリセット
                }
            });
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('myForm');
    const submitButton = document.getElementById('submitButton');

    form.addEventListener('submit', function (event) {
        // ボタンを無効にして連打を防ぐ
        submitButton.disabled = true;
        
        // ボタンのテキストを変えて処理中であることを示す（任意）
        submitButton.textContent = '送信中...';
    });
});



    </script>
@stop
