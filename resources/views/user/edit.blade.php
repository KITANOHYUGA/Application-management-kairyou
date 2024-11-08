@extends('layouts.app')

@section('title', 'ユーザー編集')

@section('content')
    <div class="container">
        <h1 class="my-4">ユーザー編集</h1>

        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label">名前</label>
                        <input type="text" id="name" name="name" value="{{ $user->name }}" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">メールアドレス</label>
                        <input type="email" id="email" name="email" value="{{ $user->email }}" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">パスワード (必要な場合のみ変更)</label>
                        <input type="password" id="password" name="password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="auth" class="form-label">権限</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auth" name="auth" value="1" {{ $user->auth == 1 ? 'checked' : '' }}>
                            <label class="form-check-label" for="auth">管理者権限を付与する</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success">更新</button>
                </form>
            </div>
        </div>

        <div class="mt-4">
            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-flex justify-content-between">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn btn-danger">ユーザーを削除</button>

                <a href="{{ url('/users/list') }}" class="btn btn-primary">ユーザー一覧に戻る</a>
            </form>
        </div>
    </div>
@endsection
