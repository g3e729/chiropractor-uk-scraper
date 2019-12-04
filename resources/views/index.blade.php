
<form method="POST" action="{{ route('chiropractor.index') }}">
	@csrf

	<label>
		Select Code:
		<select name="postcode_id">
			<option selected disabled>Choose One</option>
			@foreach ($postcodes as $postcode)
				<option value="{{ $postcode->id }}">{{ $postcode->postcode }}</option>
			@endforeach
		</select>
	</label>

	<button type="submit">Retrieve</button>
</form>