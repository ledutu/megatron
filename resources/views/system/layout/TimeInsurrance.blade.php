<style>
	.select-time option { color:black; }
</style>
<select class="form-control select-time" name="time">
  @if(Session('user')->user_Level == 1)
  @endif
  @for($i = 0;$i < 9; $i++)
                     <option value="{{"0".$i}}:00 AM UTC - {{"0".($i+1)}}:00 AM UTC">{{"0".$i}}:00 AM UTC - {{"0".($i+1)}}:00 AM UTC</option>
@endfor
<option value="09:00 AM UTC - 10:00 AM UTC">09:00 AM UTC - 10:00 AM UTC</option>
<option value="10:00 AM UTC - 11:00 AM UTC">10:00 AM UTC - 11:00 AM UTC</option>
<option value="11:00 AM UTC - 12:00 AM UTC">11:00 AM UTC - 12:00 PM UTC</option>
<option value="">-------</option>
<option value="12:00 PM UTC - 01:00 PM UTC">12:00 PM UTC - 01:00 PM UTC</option>
@for($i = 1;$i < 9; $i++)
                   <option value="{{"0".$i}}:00 PM UTC - {{"0".($i+1)}}:00 PM UTC">{{"0".$i}}:00 PM UTC - {{"0".($i+1)}}:00 PM UTC</option>
@endfor
<option value="09:00 PM UTC - 10:00 PM UTC">09:00 PM UTC - 10:00 PM UTC</option>
<option value="10:00 PM UTC - 11:00 PM UTC">10:00 PM UTC - 11:00 PM UTC</option>
<option value="11:00 PM UTC - 00:00 AM UTC">11:00 PM UTC - 00:00 AM UTC</option>
</select>
<label for="material-select">Time Zoom</label>