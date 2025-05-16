@extends('filament::layouts.app')

@section('content')
	<div class="text-center mt-10">
		<h1 class="text-2xl font-bold">Session expired</h1>
		<p class="mt-2"> <a
				href="{{ route('filament.admin.auth.login') }}" class="underline text-primary-500">
				Back to login</a>.
		</p>
	</div>
@endsection