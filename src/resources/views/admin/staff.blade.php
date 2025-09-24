@extends('layouts.default')

@section('title', 'スタッフ一覧画面（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css')}}" />
@endsection

@section('content')
<div class="content">
    <div class="users__table-wrapper">
        <h1 class="title">スタッフ一覧</h1>
        <div class="users__table">
            <table class="users__table-inner">
                <thead>
                    <tr class="table-row__header">
                        <th class="table-header">名前</th>
                        <th class="table-header">メールアドレス</th>
                        <th class="table-header">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                    <tr class="table-row">
                        <td class="table-data">{{ $user->name ?? '' }}</td>
                        <td class="table-data">{{ $user->email ?? '' }}</td>
                        <td class="table-data">
                            <a class="detail__link" href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}">詳細</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection