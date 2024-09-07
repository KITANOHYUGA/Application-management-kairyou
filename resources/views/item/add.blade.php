@extends('adminlte::page')

@section('title', 'アプリ登録')

@section('content_header')
    <h1>アプリ登録</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-10">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                       @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                       @endforeach
                    </ul>
                </div>
            @endif

            <a href="{{ url('/items') }}" class="btn btn-primary mb-3">アプリ情報一覧に戻る</a>

            <div class="card card-primary">
                <form method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">アプリ名:</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="アプリ名" required>
                        </div>

                        @if($companies->isEmpty())
                            <!-- 会社情報が空の場合、新しい会社名の入力フィールドのみ表示 -->
                            <div class="form-group" id="new_company">
                                <label for="company_name">会社名:</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" placeholder="会社名" required>
                            </div>
                        @else
                            <!-- 会社情報が存在する場合、選択ボックスと新しい会社名入力フィールドの両方を表示 -->
                            <div class="form-group">
                                <label for="company_id">会社名:</label>
                                <select class="form-control" id="company_id" name="company_id" required>
                                    <option value="" selected>選択してください</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                    @endforeach
                                    <option value="new">会社名を追加</option>
                                </select>
                            </div>

                            <div class="form-group" id="new_company" style="display:none;">
                                <input type="text" class="form-control" id="company_name" name="company_name" placeholder="会社名">
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="icon">アイコン画像:</label>
                                <div class="custom-file" style="margin-bottom: 8px;">
                                    <input type="file" class="form-control" id="icon" name="icon" style="padding-top: 4px; padding-bottom: 4px;">
                                </div>
                        </div>


                        <div class="form-group">
                            <label>価格:</label>
                                <div>
                                    <label><input type="radio" name="price_option" value="free" checked> 無料</label>
                                    <label><input type="radio" name="price_option" value="custom"> 価格を入力</label>
                                </div>
                            <input type="number" name="price" id="price" class="form-control" style="display: none;" value="{{ old('price') }}">
                        </div>

                        <div class="form-group">
                            <label for="stock">ダウンロード数/万:</label>
                            <input type="text" class="form-control" id="stock" name="stock" placeholder="ダウンロード数/万" required>
                        </div>

                        <div class="form-group">
                            <label for="comment">コメント</label>
                            <input type="text" class="form-control" id="comment" name="comment" placeholder="コメント">
                        </div>

                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">登録</button>
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


    </script>
@stop
