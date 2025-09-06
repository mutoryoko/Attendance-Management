@extends('layouts.default')

@section('title', '出勤登録画面')

@section('css')
    <link rel="stylesheet" href="" />
@endsection

@section('content')
    <div class="content">
        <form class="attendance-form" action="" method="POST">
            @csrf
            <div>ステータス</div>
            日付
            時間
            <button>出勤</button>
        </form>
    </div>
@endsection